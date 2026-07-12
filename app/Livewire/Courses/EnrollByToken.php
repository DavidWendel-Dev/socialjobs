<?php

declare(strict_types=1);

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Landing pública para convite de curso por token.
 *
 * - Se logado como candidato → cria Enrollment e redireciona pra courses.show
 * - Se não logado → guarda intended URL e pede login/cadastro
 */
#[Layout('layouts.app')]
#[Title('Convite de curso · SocialJobs')]
class EnrollByToken extends Component
{
    public string $token = '';
    public ?Course $course = null;
    public string $errorState = ''; // '', 'not_found', 'needs_login', 'company_user'

    public function mount(string $token): void
    {
        $this->token  = $token;
        $this->course = Course::query()
            ->with('companyProfile')
            ->where('access_token', $token)
            ->first();

        if (! $this->course) {
            $this->errorState = 'not_found';
            return;
        }

        if (! auth()->check()) {
            session(['url.intended' => route('courses.enroll-by-token', ['token' => $token])]);
            session(['pending_course_token' => $token]);
            $this->errorState = 'needs_login';
            return;
        }

        $user = auth()->user();

        if (($user->type ?? 'candidate') === 'company') {
            $this->errorState = 'company_user';
            return;
        }

        Enrollment::query()->firstOrCreate([
            'user_id'   => $user->id,
            'course_id' => $this->course->id,
        ]);

        $this->redirectRoute(
            'courses.show',
            ['course' => $this->course->slug],
            navigate: false
        );
    }

    public function render(): View
    {
        return view('livewire.courses.enroll-by-token');
    }
}
