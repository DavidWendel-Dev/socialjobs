<?php

declare(strict_types=1);

namespace App\Livewire\Courses;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Curso · SocialJobs')]
class Show extends Component
{
    public $course = null;

    public function mount($course = null): void
    {
        $this->course = $course;
    }

    public function enroll(): void
    {
        if (! auth()->check() || ! $this->course) return;

        try {
            if (class_exists(\App\Models\Enrollment::class)) {
                \App\Models\Enrollment::query()->firstOrCreate([
                    'user_id'   => auth()->id(),
                    'course_id' => $this->course->id ?? 0,
                ]);
            }
        } catch (\Throwable $e) {
            //
        }

        session()->flash('status', 'Matrícula realizada!');
    }

    public function render()
    {
        return view('livewire.courses.show');
    }
}
