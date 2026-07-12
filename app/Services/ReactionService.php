<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Gerencia reações polimórficas e concede XP ao dono do conteúdo reagido.
 */
class ReactionService
{
    public function __construct(private PointsService $pointsService)
    {
    }

    /**
     * Cria ou atualiza a reação do usuário no target.
     * Se já existia com o mesmo tipo, mantém.
     *
     * @return array{action:'created'|'updated'|'unchanged', reaction:Reaction}
     */
    public function react(User $user, Model $target, string $type): array
    {
        $allowed = array_keys((array) config('reactions.types', []));
        if (! in_array($type, $allowed, true)) {
            $type = (string) config('reactions.default', 'like');
        }

        $existing = Reaction::query()
            ->where('user_id', $user->id)
            ->where('reactable_type', $target->getMorphClass())
            ->where('reactable_id', $target->getKey())
            ->first();

        if ($existing) {
            if ($existing->type === $type) {
                return ['action' => 'unchanged', 'reaction' => $existing];
            }
            $existing->update(['type' => $type]);
            $this->invalidateCache($target);

            return ['action' => 'updated', 'reaction' => $existing];
        }

        $reaction = Reaction::create([
            'user_id'        => $user->id,
            'reactable_type' => $target->getMorphClass(),
            'reactable_id'   => $target->getKey(),
            'type'           => $type,
        ]);

        // Concede XP ao dono do target (se houver e não for o próprio usuário)
        $ownerId = $target->user_id ?? null;
        if ($ownerId && (int) $ownerId !== (int) $user->id) {
            $owner = User::find($ownerId);
            if ($owner) {
                $this->pointsService->award(
                    $owner,
                    'reaction.received',
                    $target,
                    // dedupe garante que a mesma reação não pague XP duas vezes
                    'reaction.received:' . $reaction->id
                );

                // Notifica o dono se o target for um Post
                if ($target instanceof \App\Models\Post) {
                    $owner->notify(new \App\Notifications\NewReactionNotification($user, $target, $type));
                }
            }
        }

        $this->invalidateCache($target);

        return ['action' => 'created', 'reaction' => $reaction];
    }

    /**
     * Remove a reação do usuário no target (se existir).
     */
    public function unreact(User $user, Model $target): bool
    {
        $deleted = Reaction::query()
            ->where('user_id', $user->id)
            ->where('reactable_type', $target->getMorphClass())
            ->where('reactable_id', $target->getKey())
            ->delete();

        if ($deleted > 0) {
            $this->invalidateCache($target);
        }

        return $deleted > 0;
    }

    /**
     * Contagens por tipo — cacheada por 60s.
     *
     * @return array<string,int>
     */
    public function counts(Model $target): array
    {
        return Cache::remember($this->cacheKey($target), 60, function () use ($target) {
            return Reaction::query()
                ->where('reactable_type', $target->getMorphClass())
                ->where('reactable_id', $target->getKey())
                ->selectRaw('type, COUNT(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type')
                ->map(fn ($v) => (int) $v)
                ->all();
        });
    }

    private function invalidateCache(Model $target): void
    {
        Cache::forget($this->cacheKey($target));
    }

    private function cacheKey(Model $target): string
    {
        return 'reactions:counts:' . $target->getMorphClass() . ':' . $target->getKey();
    }
}
