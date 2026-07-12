<?php

declare(strict_types=1);

namespace App\Livewire\Company\Courses;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonCompletion;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Matrículas do curso · SocialJobs')]
class Enrollments extends Component
{
    public Course $course;

    public bool $showInviteModal = false;

    #[Validate('required|email|max:191')]
    public string $inviteEmail = '';

    public ?string $lastInviteResult = null;

    public function mount(Course $course): void
    {
        $cp = auth()->user()?->companyProfile;
        abort_unless($cp !== null, 403);
        abort_unless(
            $course->owner_type === 'company'
                && (int) $course->company_profile_id === (int) $cp->id,
            403
        );

        $this->course = $course;
    }

    public function openInviteModal(): void
    {
        $this->reset(['inviteEmail', 'lastInviteResult']);
        $this->resetErrorBag();
        $this->showInviteModal = true;
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
    }

    public function invite(): void
    {
        $this->validate();

        $user = User::query()->where('email', $this->inviteEmail)->first();

        if ($user) {
            Enrollment::query()->firstOrCreate([
                'user_id'   => $user->id,
                'course_id' => $this->course->id,
            ]);
            $this->lastInviteResult = 'Candidato matriculado com sucesso!';
        } else {
            $this->lastInviteResult = 'Este email ainda não tem cadastro. Envie o link de convite abaixo para o candidato criar a conta e entrar automaticamente no curso.';
        }
    }

    public function render(): View
    {
        $lessonIds = $this->course->modules()
            ->with('lessons:id,module_id')
            ->get()
            ->flatMap(fn ($m) => $m->lessons->pluck('id'))
            ->all();
        $totalLessons = count($lessonIds);

        $enrollments = $this->course->enrollments()
            ->with('user')
            ->latest()
            ->get()
            ->map(function (Enrollment $e) use ($lessonIds, $totalLessons) {
                $completed = $totalLessons > 0
                    ? LessonCompletion::query()
                        ->where('user_id', $e->user_id)
                        ->whereIn('lesson_id', $lessonIds)
                        ->count()
                    : 0;
                $progress = $totalLessons > 0
                    ? (int) round(($completed / $totalLessons) * 100)
                    : 0;
                $e->setAttribute('_completed', $completed);
                $e->setAttribute('_total_lessons', $totalLessons);
                $e->setAttribute('_progress', $progress);
                return $e;
            });

        return view('livewire.company.courses.enrollments', [
            'enrollments'  => $enrollments,
            'totalLessons' => $totalLessons,
        ]);
    }
}
