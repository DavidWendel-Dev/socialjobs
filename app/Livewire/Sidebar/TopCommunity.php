<?php

declare(strict_types=1);

namespace App\Livewire\Sidebar;

use App\Models\User;
use App\Models\UserStat;
use Livewire\Component;

/**
 * Sidebar contextual para páginas de pontos/leaderboard.
 * Mostra o top 5 da comunidade.
 */
class TopCommunity extends Component
{
    public int $limit = 5;

    public function render()
    {
        $top = UserStat::query()
            ->join('users', 'users.id', '=', 'user_stats.user_id')
            ->where('users.type', '!=', 'admin')
            ->orderByDesc('user_stats.total_xp')
            ->limit($this->limit)
            ->select('user_stats.*')
            ->with('user')
            ->get();

        return view('livewire.sidebar.top-community', [
            'top' => $top,
        ]);
    }
}
