<?php

declare(strict_types=1);

namespace App\Livewire\Points;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Ranking · SocialJobs')]
class Leaderboard extends Component
{
    public string $scope = 'global'; // global|week|followers

    public function setScope(string $scope): void
    {
        $this->scope = in_array($scope, ['global', 'week', 'followers'], true) ? $scope : 'global';
    }

    public function render()
    {
        $users = collect();
        try {
            if (class_exists(\App\Models\UserStat::class)) {
                $users = \App\Models\UserStat::query()
                    ->orderByDesc('xp')
                    ->take(100)
                    ->get();
            } elseif (class_exists(\App\Models\User::class)) {
                $users = \App\Models\User::query()->take(100)->get();
            }
        } catch (\Throwable $e) {
            //
        }

        return view('livewire.points.leaderboard', ['users' => $users]);
    }
}
