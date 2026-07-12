<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PointEvent;
use App\Models\User;
use App\Models\UserStat;

/**
 * Calcula níveis/rank do usuário com base em config/points.php.
 */
class LevelService
{
    /**
     * Determina nível + próxima faixa a partir de um total de XP.
     *
     * @return array{
     *     level:int,
     *     name:string,
     *     ring_color:string,
     *     progress_to_next:float,
     *     next_at:int
     * }
     */
    public function computeLevel(int $totalXp): array
    {
        $levels = (array) config('points.levels', []);
        // Garante ordenação por chave (XP mínimo)
        ksort($levels, SORT_NUMERIC);

        $keys = array_keys($levels);

        $current = $levels[0] ?? ['level' => 1, 'name' => 'Novato', 'ring_color' => '#94A3B8'];
        $currentThreshold = 0;
        $nextThreshold    = null;

        foreach ($keys as $threshold) {
            if ($totalXp >= (int) $threshold) {
                $current          = $levels[$threshold];
                $currentThreshold = (int) $threshold;
            } else {
                $nextThreshold = (int) $threshold;
                break;
            }
        }

        if ($nextThreshold === null) {
            // Já está na maior faixa
            $progress = 1.0;
            $nextAt   = $currentThreshold;
        } else {
            $span     = max(1, $nextThreshold - $currentThreshold);
            $progress = max(0.0, min(1.0, ($totalXp - $currentThreshold) / $span));
            $nextAt   = $nextThreshold;
        }

        return [
            'level'            => (int) ($current['level'] ?? 1),
            'name'             => (string) ($current['name'] ?? 'Novato'),
            'ring_color'       => (string) ($current['ring_color'] ?? '#94A3B8'),
            'progress_to_next' => round($progress, 4),
            'next_at'          => $nextAt,
        ];
    }

    /**
     * Recalcula total_xp somando point_events e persiste user_stats.
     */
    public function refresh(User $user): UserStat
    {
        $totalXp = (int) PointEvent::where('user_id', $user->id)->sum('xp');
        $info    = $this->computeLevel($totalXp);

        return UserStat::updateOrCreate(
            ['user_id' => $user->id],
            [
                'total_xp'   => $totalXp,
                'level'      => $info['level'],
                'updated_at' => now(),
            ]
        );
    }
}
