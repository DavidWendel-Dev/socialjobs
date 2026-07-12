<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Mention;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Gerencia menções (@usuario) em conteúdos textuais (posts, comentários, mensagens).
 * Responsabilidades:
 *   - extrair @usernames de um texto
 *   - resolver quais usernames são reais
 *   - sincronizar a tabela `mentions` com o conteúdo
 *   - renderizar o texto convertendo @usuario em link HTML clicável
 *   - buscar sugestões para o autocomplete
 */
class MentionService
{
    /**
     * Regex para localizar menções no texto.
     * Aceita username com letras, números, ponto, hífen e underscore (min 2 chars).
     * @ deve estar no início da string ou precedido por espaço/quebra de linha.
     */
    public const MENTION_REGEX = '/(?:^|\s)@([a-z0-9][a-z0-9._-]{1,39})/iu';

    /**
     * Extrai todos os @usernames únicos de um texto (sem o @).
     *
     * @return string[]
     */
    public function extractUsernames(string $text): array
    {
        if (! preg_match_all(self::MENTION_REGEX, $text, $matches)) {
            return [];
        }
        return array_values(array_unique(array_map(
            fn ($u) => mb_strtolower($u),
            $matches[1]
        )));
    }

    /**
     * Resolve os usernames em users reais. Retorna Collection<int, User>
     * indexada por username (lowercase).
     */
    public function resolveUsers(array $usernames): \Illuminate\Support\Collection
    {
        if (empty($usernames)) {
            return collect();
        }
        return User::query()
            ->whereIn('username', $usernames)
            ->get()
            ->keyBy(fn (User $u) => mb_strtolower($u->username ?? ''));
    }

    /**
     * Cria/atualiza registros na tabela `mentions` para o conteúdo dado.
     * Chame após criar/editar um Post ou Comment com body possivelmente contendo menções.
     */
    public function syncMentions(Model $mentionable, string $text, User $mentioner): void
    {
        $usernames = $this->extractUsernames($text);
        $users     = $this->resolveUsers($usernames);

        // Limpa menções anteriores deste conteúdo (evita duplicatas / lixo)
        Mention::query()
            ->where('mentionable_type', $mentionable->getMorphClass())
            ->where('mentionable_id', $mentionable->getKey())
            ->delete();

        foreach ($users as $target) {
            // Não permite auto-menção
            if ((int) $target->id === (int) $mentioner->id) {
                continue;
            }

            Mention::create([
                'mentioner_id'     => $mentioner->id,
                'mentioned_id'     => $target->id,
                'mentionable_type' => $mentionable->getMorphClass(),
                'mentionable_id'   => $mentionable->getKey(),
            ]);
        }
    }

    /**
     * Converte @usuario em <a href="/u/usuario">@usuario</a> no HTML final.
     * Faz `e()` no restante do texto e converte quebras de linha em <br>.
     */
    public function renderHtml(?string $text, string $baseUrl = '/u/'): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        // Escapa HTML primeiro
        $escaped = e($text);

        // Substitui menções por links (procurando no texto já escapado)
        $html = preg_replace_callback(self::MENTION_REGEX, function ($m) use ($baseUrl) {
            $prefix   = $m[0][0] === '@' ? '' : $m[0][0]; // se veio espaço no início, preserva
            $username = $m[1];
            $link     = '<a href="' . $baseUrl . e($username) . '"'
                      . ' class="font-semibold text-brand-600 hover:underline dark:text-brand-400">'
                      . '@' . e($username) . '</a>';
            return $prefix . $link;
        }, $escaped);

        return nl2br($html ?? $escaped);
    }

    /**
     * Sugere users para autocomplete a partir de uma query parcial (sem o @).
     * Se a query estiver vazia, retorna os users mais populares — assim o dropdown
     * aparece logo que o usuário digita apenas "@".
     * Ordena por popularidade (total_xp DESC) e limita a $limit.
     *
     * @return array<int, array{id:int, username:string, name:string, headline:?string, avatar_url:?string}>
     */
    public function suggest(string $query, int $limit = 6, ?int $excludeUserId = null): array
    {
        $query = trim($query);
        $like  = $query . '%';

        return User::query()
            ->whereNotNull('username')
            ->when($excludeUserId, fn ($q) => $q->where('users.id', '!=', $excludeUserId))
            // Quando há query, filtra; sem query, retorna os mais populares (top XP)
            ->when($query !== '', function ($q) use ($like) {
                $q->where(function ($qq) use ($like) {
                    $qq->where('username', 'like', $like)
                       ->orWhere('name', 'like', '%' . $like);
                });
            })
            ->leftJoin('user_stats', 'user_stats.user_id', '=', 'users.id')
            ->select('users.*', 'user_stats.total_xp')
            ->orderByRaw('COALESCE(user_stats.total_xp, 0) DESC')
            ->orderBy('users.name')
            ->limit($limit)
            ->get()
            ->map(fn (User $u) => [
                'id'         => $u->id,
                'username'   => (string) $u->username,
                'name'       => (string) $u->name,
                'headline'   => $u->headline,
                'avatar_url' => $u->avatar_url,
            ])
            ->all();
    }
}
