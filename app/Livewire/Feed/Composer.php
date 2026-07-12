<?php

declare(strict_types=1);

namespace App\Livewire\Feed;

use App\Models\Post;
use App\Models\PostMedia;
use App\Services\DeezerService;
use App\Services\MentionService;
use App\Services\PointsService;
use App\Support\Media;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Composer extends Component
{
    use WithFileUploads;

    /**
     * Texto do post. NÃO usamos #[Validate] aqui porque o Livewire validaria
     * automaticamente a cada `$wire.set('body', ...)` — resultando em erro
     * "validation.required" quando o usuário digita e apaga tudo. A validação
     * roda apenas ao clicar em Publicar (método save()).
     */
    public string $body = '';

    /**
     * Array de imagens já confirmadas (aparecem no preview).
     * Cada item é um TemporaryUploadedFile do Livewire.
     */
    public array $images = [];

    /**
     * Buffer temporário para novos uploads.
     * O input <file multiple> escreve aqui; o hook updatedNewImages()
     * transfere para $images (assim conseguimos ACUMULAR imagens em
     * múltiplas seleções sem perder as anteriores).
     */
    public $newImages = [];

    /**
     * Tipo do post — visual/organização. Valores permitidos batem com o enum
     * da migration `add_type_to_posts_table` (post|article|insight|question|showcase).
     */
    #[Validate('required|string|in:post,article,insight,question,showcase')]
    public string $type = 'post';

    /**
     * Se marcado, o post aparece na seção "Publicações em destaque"
     * do Currículo Digital do candidato.
     */
    public bool $isFeatured = false;

    /** Metadados de uma música do Deezer se o usuário anexou (salvo em link_preview). */
    public ?array $selectedMusic = null;

    /** Termo digitado no buscador de músicas. */
    public string $musicQuery = '';

    /** Resultados atuais do Deezer para o musicQuery (renderizados no painel). */
    public array $musicResults = [];

    /**
     * Definições dos tipos de post — usadas para renderizar o seletor e o badge.
     *
     * @var array<string, array{label:string, icon:string, color:string}>
     */
    public array $postTypes = [
        'post'     => ['label' => 'Post',      'icon' => 'sparkles',  'color' => 'slate'],
        'article'  => ['label' => 'Artigo',    'icon' => 'book',      'color' => 'blue'],
        'insight'  => ['label' => 'Insight',   'icon' => 'sparkles',  'color' => 'brand'],
        'question' => ['label' => 'Pergunta',  'icon' => 'message',   'color' => 'amber'],
        'showcase' => ['label' => 'Projeto',   'icon' => 'briefcase', 'color' => 'accent'],
    ];

    /* ============================================================
     |  Menções (@usuario) — autocomplete
     |============================================================ */

    public string $mentionQuery = '';
    public array  $mentionResults = [];

    public function updatedMentionQuery(): void
    {
        // Query vazia = mostra os users mais populares (dropdown ao digitar apenas "@")
        $this->mentionResults = app(MentionService::class)
            ->suggest($this->mentionQuery, 6, auth()->id());
    }

    public function closeMentions(): void
    {
        $this->mentionQuery   = '';
        $this->mentionResults = [];
    }

    public function setType(string $type): void
    {
        $this->type = array_key_exists($type, $this->postTypes) ? $type : 'post';
    }

    /**
     * Dispara automaticamente quando musicQuery muda (via wire:model.live.debounce).
     * Chama o Deezer via nosso proxy backend (sem CORS, sem chave).
     */
    public function updatedMusicQuery(): void
    {
        $q = trim($this->musicQuery);
        if (mb_strlen($q) < 2) {
            $this->musicResults = [];
            return;
        }

        $this->musicResults = app(DeezerService::class)->searchTracks($q, 8);
    }

    /**
     * Anexa uma track do Deezer ao post que está sendo escrito.
     * Recebemos apenas o ID e recuperamos o restante dos metadados a partir
     * do próprio $musicResults ou de uma nova consulta ao Deezer.
     * Isso evita passar o array completo pelo Livewire (que escapa mal).
     */
    public function attachMusicById(int $trackId): void
    {
        if ($trackId <= 0) {
            return;
        }

        // Procura o track já carregado nos resultados atuais
        $found = collect($this->musicResults)->firstWhere('id', $trackId);

        // Fallback: se por algum motivo não estiver, busca de novo
        if (! $found && $this->musicQuery !== '') {
            $all = app(DeezerService::class)->searchTracks($this->musicQuery, 8);
            $found = collect($all)->firstWhere('id', $trackId);
        }

        if (! $found) {
            return;
        }

        $this->selectedMusic = [
            'id'       => (int) ($found['id'] ?? 0),
            'title'    => (string) ($found['title'] ?? ''),
            'artist'   => (string) ($found['artist'] ?? ''),
            'album'    => (string) ($found['album'] ?? ''),
            'cover'    => (string) ($found['cover'] ?? ''),
            'preview'  => (string) ($found['preview'] ?? ''),
            'duration' => (int) ($found['duration'] ?? 0),
            'link'     => (string) ($found['link'] ?? ''),
        ];

        // Fecha o painel: limpa a query, resultados somem
        $this->musicQuery   = '';
        $this->musicResults = [];

        // Sinaliza para o Alpine do painel de resultados parar qualquer prévia tocando
        $this->dispatch('remove-music-preview');
    }

    public function removeMusic(): void
    {
        $this->selectedMusic = null;
    }

    /**
     * Remove uma imagem específica da lista de uploads (antes de publicar).
     */
    public function removeImage(int $index): void
    {
        if (isset($this->images[$index])) {
            unset($this->images[$index]);
            // Reindex para o Livewire não confundir chaves
            $this->images = array_values($this->images);
        }
    }

    /**
     * Chamado automaticamente pelo Livewire toda vez que $newImages muda
     * (quando o usuário seleciona novos arquivos no <input type="file" multiple>).
     *
     * Fluxo:
     *   1) usuário seleciona 3 fotos → $newImages tem 3 items
     *   2) mesclamos com $images (que talvez já tenha 2 anteriores)
     *   3) $images fica com 5 items, $newImages volta a ser vazio
     *   4) preview do template mostra todas — usuário pode clicar de novo
     *      para adicionar mais até chegar em 6.
     *
     * IMPORTANTE: no Livewire 3, o hook pode disparar em fases (upload
     * inicia → upload finaliza). Precisamos ignorar disparos onde
     * $newImages ainda não é array válido — o próximo disparo trará os
     * TemporaryUploadedFile prontos. E precisamos garantir que o array
     * $images seja re-emitido no snapshot pra que o preview renderize.
     */
    public function updatedNewImages(): void
    {
        // Normaliza para array (input multiple às vezes chega como single)
        if (! is_array($this->newImages)) {
            $this->newImages = $this->newImages ? [$this->newImages] : [];
        }

        // Filtra apenas TemporaryUploadedFile já finalizados
        $valid = array_values(array_filter(
            $this->newImages,
            fn ($item) => $item instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
                && $item->isValid()
        ));

        if (empty($valid)) {
            // Nenhum arquivo pronto ainda — o hook vai rodar de novo em breve
            return;
        }

        // Valida rapidamente cada nova imagem
        try {
            validator(['newImages' => $valid], [
                'newImages.*' => 'image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            ], [
                'newImages.*.image' => 'Um dos arquivos não é uma imagem válida.',
                'newImages.*.mimes' => 'Use JPG, PNG, WEBP ou GIF.',
                'newImages.*.max'   => 'Cada imagem pode ter no máximo 5 MB.',
            ])->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->newImages = [];
            $this->addError('images', $e->validator->errors()->first());
            return;
        }

        // Evita duplicar se o hook disparar 2x com o mesmo arquivo
        $existingHashes = array_map(
            fn ($f) => $f instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
                ? $f->getFilename()
                : spl_object_id($f),
            $this->images
        );

        foreach ($valid as $file) {
            if (! in_array($file->getFilename(), $existingHashes, true)) {
                $this->images[] = $file;
            }
        }

        // Reindexa
        $this->images = array_values($this->images);

        // Limita a 6 imagens totais
        if (count($this->images) > 6) {
            $this->images = array_slice($this->images, 0, 6);
            session()->flash('composer-warning', 'Máximo de 6 imagens. As extras foram descartadas.');
        }

        // Limpa o buffer
        $this->newImages = [];

        // Força um segundo commit pra o cliente receber as temporaryUrl()
        // já geradas — sem isso, na PRIMEIRA imagem o snapshot volta com
        // URLs vazias e o preview só aparece na próxima interação.
        $this->dispatch('composer-images-updated');
    }

    /**
     * Segundo commit — apenas re-renderiza. Chamado via $wire.$dispatch
     * no cliente logo depois que o hook updatedNewImages termina.
     */
    #[\Livewire\Attributes\On('composer-images-updated')]
    public function refreshImages(): void
    {
        // no-op — o simples fato de rodar já força novo snapshot
    }

    /**
     * Publica o post e concede XP. Emite evento post-created para a Timeline recarregar.
     */
    public function save(): void
    {
        if (! auth()->check()) {
            return;
        }

        // Regras validadas apenas ao publicar (não em tempo real).
        // Aceitamos post sem texto SE tiver pelo menos uma imagem ou música anexada.
        $hasBody   = trim($this->body) !== '';
        $hasImages = ! empty($this->images);
        $hasMusic  = ! empty($this->selectedMusic);

        if (! $hasBody && ! $hasImages && ! $hasMusic) {
            $this->addError('body', 'Escreva algo ou anexe uma imagem/música para publicar.');
            return;
        }

        $this->validate([
            'body' => 'nullable|string|max:5000',
            'type' => 'required|string|in:post,article,insight,question,showcase',
        ]);

        // Valida as imagens (regras aninhadas — separado do #[Validate])
        if (! empty($this->images)) {
            $this->validate([
                'images'   => 'array|max:6',
                'images.*' => 'image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            ]);

            // ============================================================
            //  Moderação NSFW via Oanor — bloqueia nudez antes de salvar.
            //  Falhar-aberto: se a API estiver offline, o post passa (log).
            // ============================================================
            $scanner = app(\App\Services\NsfwScanner::class);
            foreach ($this->images as $i => $image) {
                if (! $image) continue;
                if (! $scanner->isSafe($image)) {
                    $this->addError('images', 'A imagem ' . ($i + 1) . ' foi bloqueada por conter conteúdo impróprio. Remova e tente novamente.');
                    return;
                }
            }
        }

        // Salvamos o body como texto puro. A escapagem para HTML acontece
        // na hora de renderizar (MentionService::renderHtml faz e() + nl2br).
        // Isso evita duplo-encoding e envelopamentos indesejados como <p>...</p>
        // que o HtmlPurifier faria automaticamente em modo padrão.
        $body = trim($this->body);

        $post = DB::transaction(function () use ($body) {
            $post = Post::create([
                'user_id'      => auth()->id(),
                'body'         => $body,
                'type'         => $this->type,
                'is_featured'  => $this->isFeatured,
                'visibility'   => 'public',
                'link_preview' => $this->selectedMusic
                    ? ['kind' => 'deezer', 'track' => $this->selectedMusic]
                    : null,
            ]);

            // Cria um registro em post_media para cada imagem
            // (o post pode ter 1..6 imagens; se o usuário não anexou, o array é vazio)
            $order = 0;
            foreach ($this->images as $image) {
                if (! $image) {
                    continue;
                }
                $path = Media::store($image, 'posts');
                PostMedia::create([
                    'post_id' => $post->id,
                    'path'    => $path,
                    'type'    => 'image',
                    'order'   => $order++,
                ]);
            }

            return $post;
        });

        // Registra menções no texto (persiste na tabela `mentions`)
        app(MentionService::class)->syncMentions($post, $post->body, auth()->user());

        $svc = app(PointsService::class);
        $svc->award(auth()->user(), 'post.first');
        $svc->award(
            auth()->user(),
            'post.created',
            $post,
            'post.created:' . $post->id
        );

        // Limpa tudo
        $this->reset(['body', 'images', 'newImages', 'selectedMusic', 'musicQuery', 'musicResults', 'mentionQuery', 'mentionResults', 'isFeatured']);
        $this->type = 'post';

        $this->dispatch('post-created', postId: $post->id);
        session()->flash('status', 'Publicado! 🎉');
    }

    public function render()
    {
        return view('livewire.feed.composer');
    }
}
