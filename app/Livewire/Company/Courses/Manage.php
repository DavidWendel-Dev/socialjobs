<?php

declare(strict_types=1);

namespace App\Livewire\Company\Courses;

use App\Models\Course;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Meus cursos · SocialJobs')]
class Manage extends Component
{
    public function delete(int $id): void
    {
        $cp = auth()->user()?->companyProfile;
        if (! $cp) {
            return;
        }

        $affected = Course::query()
            ->where('id', $id)
            ->where('owner_type', 'company')
            ->where('company_profile_id', $cp->id)
            ->delete();

        if ($affected > 0) {
            session()->flash('status', 'Curso removido.');
        } else {
            session()->flash('error', 'Curso não encontrado ou sem permissão.');
        }
    }

    public function render(): View
    {
        $cp = auth()->user()?->companyProfile;

        $courses = $cp
            ? Course::query()
                ->companyOwned()
                ->where('company_profile_id', $cp->id)
                ->withCount(['enrollments', 'modules'])
                ->latest()
                ->get()
            : collect();

        return view('livewire.company.courses.manage', [
            'courses' => $courses,
        ]);
    }
}
