<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\User;
use App\Services\CurriculumService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Currículo Digital público (sem login necessário).
 * URL: /cv/{username}
 *
 * Se o dono do perfil quiser esconder, futuramente poderemos adicionar
 * um flag `cv_public` — por enquanto todo candidato tem CV público.
 */
#[Layout('layouts.cv')]
#[Title('Currículo Digital · SocialJobs')]
class CurriculumPublic extends Component
{
    public User $user;
    public array $cv = [];

    public function mount(string $username): void
    {
        $this->user = User::query()
            ->where('username', $username)
            ->where('type', 'candidate')
            ->firstOrFail();

        $this->cv = app(CurriculumService::class)->buildFor($this->user);

        // Registra view do perfil (CV público conta como visita ao perfil)
        app(\App\Services\ViewTrackerService::class)->trackProfile($this->user->id);
    }

    public function render(): View
    {
        return view('livewire.profile.curriculum-public', $this->cv);
    }
}
