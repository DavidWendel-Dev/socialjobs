<?php

declare(strict_types=1);

namespace App\Livewire\Courses;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cursos · SocialJobs')]
class Catalog extends Component
{
    #[Url]
    public string $category = '';
    #[Url]
    public string $level = '';

    public function render()
    {
        $courses = collect();
        try {
            if (class_exists(\App\Models\Course::class)) {
                $q = \App\Models\Course::query()
                    ->where(function ($sub) {
                        // Cursos da plataforma sempre entram
                        $sub->where('owner_type', 'platform')
                            // Cursos de empresa só entram se visibility = public
                            ->orWhere(function ($cq) {
                                $cq->where('owner_type', 'company')
                                   ->where('visibility', 'public');
                            });
                    });
                if ($this->category) $q->where('category', $this->category);
                if ($this->level) $q->where('level', $this->level);
                $courses = $q->latest()->take(30)->get();
            }
        } catch (\Throwable $e) {
            //
        }

        return view('livewire.courses.catalog', ['courses' => $courses]);
    }
}
