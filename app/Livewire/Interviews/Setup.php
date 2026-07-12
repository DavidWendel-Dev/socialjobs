<?php

declare(strict_types=1);

namespace App\Livewire\Interviews;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Simulador de entrevistas · SocialJobs')]
class Setup extends Component
{
    public string $role = '';
    public string $seniority = 'Pleno';
    public string $mode = 'text'; // text|voice
    public ?int $job_id = null;

    public function start(): void
    {
        $this->validate([
            'role'      => ['required', 'string', 'max:120'],
            'seniority' => ['required', 'string'],
            'mode'      => ['required', 'in:text,voice'],
        ]);

        $sessionId = 0;
        try {
            if (class_exists(\App\Services\InterviewSimulatorService::class) && auth()->check()) {
                $svc = app(\App\Services\InterviewSimulatorService::class);
                if (method_exists($svc, 'start')) {
                    $session = $svc->start(auth()->user(), [
                        'role'      => $this->role,
                        'seniority' => $this->seniority,
                        'mode'      => $this->mode,
                        'job_id'    => $this->job_id,
                    ]);
                    $sessionId = is_object($session) ? ($session->id ?? 0) : (int) $session;
                }
            }
        } catch (\Throwable $e) {
            //
        }

        if ($sessionId) {
            $this->redirectRoute('interviews.room', ['session' => $sessionId], navigate: true);
        } else {
            session()->flash('status', 'Serviço de simulador indisponível no momento.');
        }
    }

    public function render()
    {
        return view('livewire.interviews.setup');
    }
}
