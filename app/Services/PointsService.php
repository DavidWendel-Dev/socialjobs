<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PointEvent;
use App\Models\User;
use App\Models\UserStat;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Concede/revoga XP para o usuário respeitando limites de config/points.php.
 */
class PointsService
{
    public function __construct(private LevelService $levelService)
    {
    }

    /**
     * Concede XP para uma ação. Retorna o PointEvent criado ou null se recusado
     * (limite diário atingido, ação once já concedida, ou dedupe_key conflita).
     */
    public function award(User $user, string $action, ?Model $subject = null, ?string $dedupeKey = null): ?PointEvent
    {
        // IMPORTANTE: usamos acesso direto ao array porque as chaves de ação
        // contêm pontos (ex.: "comment.created") e `config('points.actions.foo.bar')`
        // interpretaria o ponto como acesso aninhado, retornando null.
        $allActions = (array) config('points.actions', []);
        $cfg        = $allActions[$action] ?? null;

        if (! is_array($cfg)) {
            return null;
        }

        $xp         = (int) ($cfg['xp'] ?? 0);
        $dailyLimit = $cfg['daily_limit'] ?? null;
        $onceOnly   = (bool) ($cfg['once'] ?? false);

        // Regra "once" — verifica se já foi concedido em algum momento
        if ($onceOnly && PointEvent::where('user_id', $user->id)->where('action', $action)->exists()) {
            return null;
        }

        // Regra "daily_limit"
        if ($dailyLimit !== null) {
            $todayCount = PointEvent::where('user_id', $user->id)
                ->where('action', $action)
                ->whereDate('created_at', CarbonImmutable::now()->toDateString())
                ->count();

            if ($todayCount >= (int) $dailyLimit) {
                return null;
            }
        }

        return DB::transaction(function () use ($user, $action, $xp, $subject, $dedupeKey) {
            // Se dedupeKey foi passado, garante unicidade — se já existir, retorna o antigo
            if ($dedupeKey !== null) {
                $existing = PointEvent::where('user_id', $user->id)
                    ->where('dedupe_key', $dedupeKey)
                    ->first();
                if ($existing) {
                    return null;
                }
            }

            $event = PointEvent::create([
                'user_id'      => $user->id,
                'action'       => $action,
                'xp'           => $xp,
                'subject_type' => $subject ? $subject->getMorphClass() : null,
                'subject_id'   => $subject?->getKey(),
                'dedupe_key'   => $dedupeKey,
                'created_at'   => now(),
            ]);

            // Recalcula stats do usuário
            $this->levelService->refresh($user);

            return $event;
        });
    }

    /**
     * Revoga um evento de pontos criando uma contra-partida com XP negativo.
     */
    public function revoke(User $user, PointEvent $originalEvent): PointEvent
    {
        $reverse = PointEvent::create([
            'user_id'      => $user->id,
            'action'       => $originalEvent->action . '.revoked',
            'xp'           => -1 * (int) $originalEvent->xp,
            'subject_type' => $originalEvent->subject_type,
            'subject_id'   => $originalEvent->subject_id,
            'dedupe_key'   => $originalEvent->dedupe_key ? $originalEvent->dedupe_key . ':revoked' : null,
            'created_at'   => now(),
        ]);

        $this->levelService->refresh($user);

        return $reverse;
    }

    /**
     * Retorna o total de XP atual do usuário (leitura direta do user_stats).
     */
    public function totalXp(User $user): int
    {
        return (int) (UserStat::query()->where('user_id', $user->id)->value('total_xp') ?? 0);
    }
}
