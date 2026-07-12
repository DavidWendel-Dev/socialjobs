<?php

declare(strict_types=1);

namespace App\Livewire\Courses;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Aula · SocialJobs')]
class Player extends Component
{
    public $course = null;
    public $lesson = null;
    public string $tab = 'content';

    public function mount($course = null, $lesson = null): void
    {
        $this->course = $course;
        $this->lesson = $lesson;
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function markComplete(): void
    {
        try {
            if (class_exists(\App\Services\CourseProgressService::class) && auth()->check() && $this->lesson) {
                $svc = app(\App\Services\CourseProgressService::class);
                if (method_exists($svc, 'completeLesson')) {
                    $svc->completeLesson(auth()->user(), $this->lesson);
                }
                if (class_exists(\App\Services\PointsService::class)) {
                    app(\App\Services\PointsService::class)->award(auth()->user(), 'lesson.completed');
                }
            }
        } catch (\Throwable $e) {
            //
        }
        session()->flash('status', 'Aula marcada como concluída.');
    }

    public function render()
    {
        return view('livewire.courses.player');
    }
}
