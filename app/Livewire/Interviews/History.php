<?php

declare(strict_types=1);

namespace App\Livewire\Interviews;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Histórico · Simulador · SocialJobs')]
class History extends Component
{
    public function render()
    {
        $sessions = collect();
        try {
            if (class_exists(\App\Models\InterviewSession::class) && auth()->check()) {
                $sessions = \App\Models\InterviewSession::query()
                    ->where('user_id', auth()->id())
                    ->latest()
                    ->get();
            }
        } catch (\Throwable $e) {
            //
        }

        return view('livewire.interviews.history', ['sessions' => $sessions]);
    }
}
