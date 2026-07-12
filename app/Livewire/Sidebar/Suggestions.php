<?php

declare(strict_types=1);

namespace App\Livewire\Sidebar;

use App\Models\Follow;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Sugestões de perfis para seguir.
 * Aparece na sidebar direita do layout, apenas para usuários logados.
 */
class Suggestions extends Component
{
    /** Quantos sugerir. */
    public int $limit = 5;

    /**
     * Segue o usuário informado. Idempotente via unique key em follows.
     */
    public function follow(int $userId): void
    {
        if (! auth()->check()) {
            return;
        }

        // Não pode seguir a si mesmo
        if (auth()->id() === $userId) {
            return;
        }

        $target = User::find($userId);
        if (! $target) {
            return;
        }

        $follow = Follow::firstOrCreate([
            'follower_id' => auth()->id(),
            'followed_id' => $userId,
        ]);

        // XP para o usuário que ganhou o seguidor (só se é novo, evita dupe)
        if ($follow->wasRecentlyCreated) {
            app(PointsService::class)->award(
                $target,
                'follower.gained',
                $target,
                'follower.gained:' . $follow->id
            );

            // Notifica o alvo do follow
            $target->notify(new \App\Notifications\NewFollowerNotification(auth()->user()));
        }
    }

    /**
     * Deixa de seguir.
     */
    public function unfollow(int $userId): void
    {
        if (! auth()->check()) {
            return;
        }

        Follow::query()
            ->where('follower_id', auth()->id())
            ->where('followed_id', $userId)
            ->delete();
    }

    /**
     * Constrói a lista de sugestões:
     * - Usuários que NÃO sou eu
     * - Usuários que NÃO estou seguindo
     * - Excluir contas admin
     * - Ordenar por total_xp DESC (perfis mais ativos primeiro), depois randômico
     */
    public function render()
    {
        $user = auth()->user();

        $suggestions = collect();
        $followingCount = 0;

        if ($user) {
            $followingIds = $user->follows()->pluck('users.id')->all();
            $followingCount = count($followingIds);
            $excludeIds   = array_merge($followingIds, [$user->id]);

            // Filtro por tipo:
            // - Candidato → sugere apenas outros candidatos
            // - Empresa   → também sugere candidatos (empresas seguem candidatos, não outras empresas)
            // Em ambos os casos, contas admin são excluídas.
            $targetType = 'candidate';

            $suggestions = User::query()
                ->whereNotIn('users.id', $excludeIds)
                ->where('users.type', $targetType)
                ->leftJoin('user_stats', 'user_stats.user_id', '=', 'users.id')
                ->select('users.*', 'user_stats.total_xp')
                ->orderByRaw('COALESCE(user_stats.total_xp, 0) DESC')
                ->orderByRaw('RAND()')
                ->limit($this->limit)
                ->get();
        }

        return view('livewire.sidebar.suggestions', [
            'suggestions'    => $suggestions,
            'followingCount' => $followingCount,
        ]);
    }
}
