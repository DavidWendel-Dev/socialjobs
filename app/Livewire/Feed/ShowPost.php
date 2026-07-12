<?php

declare(strict_types=1);

namespace App\Livewire\Feed;

use App\Models\Post;
use App\Services\ViewTrackerService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Página dedicada de um único post.
 *
 * Fluxo: usuário clica em "Abrir post" no dropdown do post-card, ou acessa
 * diretamente por link compartilhado.
 *
 * Recursos:
 *  - Card completo do post (reações, comentários, mídia, três-pontinhos)
 *  - Contagem de views (incrementa 1× por sessão / 6h; ignora autor)
 *  - Header com botão de voltar
 *  - Posts sugeridos do mesmo autor (até 3)
 *  - Ownership check e retornos amigáveis pra post inexistente/deletado
 */
#[Layout('layouts.app')]
#[Title('Post · SocialJobs')]
class ShowPost extends Component
{
    public ?Post $post = null;

    public function mount(Post $post): void
    {
        // Route model binding já resolve o post pelo ID. Se não achou → 404 automático.
        $this->post = $post->load(['user', 'media']);

        // Registra visualização (ignora autor, dedupe por sessão)
        app(ViewTrackerService::class)->trackPost($post->id, (int) $post->user_id);

        // Recarrega pra pegar o contador atualizado
        $this->post->refresh();
    }

    public function render(): View
    {
        // Outros posts do mesmo autor (até 3, mais recentes, exclui o atual)
        $morePosts = collect();
        if ($this->post && $this->post->user) {
            $morePosts = Post::query()
                ->where('user_id', $this->post->user_id)
                ->where('id', '!=', $this->post->id)
                ->where('visibility', 'public')
                ->with(['user'])
                ->latest()
                ->limit(3)
                ->get();
        }

        return view('livewire.feed.show-post', [
            'morePosts' => $morePosts,
        ]);
    }
}
