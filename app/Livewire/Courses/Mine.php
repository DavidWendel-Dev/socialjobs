<?php

declare(strict_types=1);

namespace App\Livewire\Courses;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Meus cursos · SocialJobs')]
class Mine extends Component
{
    public string $tab = 'ongoing';

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function render()
    {
        $enrollments = collect();
        try {
            if (class_exists(\App\Models\Enrollment::class) && auth()->check()) {
                $enrollments = \App\Models\Enrollment::query()
                    ->where('user_id', auth()->id())
                    ->latest()
                    ->get();
            }
        } catch (\Throwable $e) {
            //
        }

        return view('livewire.courses.mine', ['enrollments' => $enrollments]);
    }
}
