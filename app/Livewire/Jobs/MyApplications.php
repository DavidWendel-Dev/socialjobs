<?php

declare(strict_types=1);

namespace App\Livewire\Jobs;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Minhas candidaturas · SocialJobs')]
class MyApplications extends Component
{
    public function render()
    {
        $apps = collect();
        try {
            if (class_exists(\App\Models\Application::class) && auth()->check()) {
                $apps = \App\Models\Application::query()
                    ->where('user_id', auth()->id())
                    ->latest()
                    ->get();
            }
        } catch (\Throwable $e) {
            //
        }

        return view('livewire.jobs.my-applications', ['apps' => $apps]);
    }
}
