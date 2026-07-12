<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Serviço de rastreamento de visualizações.
 *
 * Regras:
 *  - Autor NUNCA conta como view do próprio conteúdo.
 *  - Cada visitante conta 1x por conteúdo em uma janela de 6 horas (via sessão do browser).
 *  - Visitantes anônimos também contam (chave é a session_id, não o user_id).
 *  - Incremento é feito com UPDATE + increment() atômico no banco.
 *
 * Uso:
 *   app(ViewTrackerService::class)->trackPost($post);
 *   app(ViewTrackerService::class)->trackProfile($user);
 */
class ViewTrackerService
{
    /** Janela de deduplicação em segundos (6 horas). */
    private const WINDOW = 21600;

    /**
     * Registra uma view em um post.
     * Retorna true se a view foi contada agora, false se foi ignorada (autor ou já contada nesta janela).
     */
    public function trackPost(int $postId, ?int $authorId = null): bool
    {
        if ($postId <= 0) {
            return false;
        }

        // Autor não conta view do próprio post
        if ($authorId !== null && auth()->id() === $authorId) {
            return false;
        }

        $sessionKey = "viewed_post_{$postId}";
        if (! $this->shouldCount($sessionKey)) {
            return false;
        }

        DB::table('posts')->where('id', $postId)->increment('views_count');
        $this->markCounted($sessionKey);

        return true;
    }

    /**
     * Registra uma view em um perfil (users.id).
     */
    public function trackProfile(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        // Usuário logado não conta view do próprio perfil
        if (auth()->id() === $userId) {
            return false;
        }

        $sessionKey = "viewed_profile_{$userId}";
        if (! $this->shouldCount($sessionKey)) {
            return false;
        }

        DB::table('users')->where('id', $userId)->increment('profile_views_count');
        $this->markCounted($sessionKey);

        return true;
    }

    /**
     * Verifica se essa view deve ser contada (não vista nesta sessão em <6h).
     */
    private function shouldCount(string $key): bool
    {
        $seenAt = Session::get($key);
        if ($seenAt === null) {
            return true;
        }
        // Se o timestamp guardado é antigo demais, conta de novo
        return (time() - (int) $seenAt) > self::WINDOW;
    }

    private function markCounted(string $key): void
    {
        Session::put($key, time());
    }
}
