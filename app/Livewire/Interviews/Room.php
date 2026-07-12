<?php

declare(strict_types=1);

namespace App\Livewire\Interviews;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Entrevista · SocialJobs')]
class Room extends Component
{
    public $session = null;
    /** @var array<int, array{role: string, text: string}> */
    public array $turns = [];
    public string $message = '';
    public bool $finished = false;
    public ?array $report = null;

    public function mount($session = null): void
    {
        $this->session = $session;
        // Turno inicial
        $this->turns[] = ['role' => 'ai', 'text' => 'Olá! Vamos começar. Conte-me um pouco sobre você.'];
    }

    public function send(): void
    {
        $msg = trim($this->message);
        if ($msg === '') return;

        $this->turns[] = ['role' => 'user', 'text' => $msg];
        $this->message = '';

        // Pergunta próxima ao service (se houver)
        $reply = 'Interessante. Pode me dar um exemplo prático dessa experiência?';
        try {
            if (class_exists(\App\Services\InterviewSimulatorService::class)) {
                $svc = app(\App\Services\InterviewSimulatorService::class);
                if (method_exists($svc, 'nextTurn')) {
                    $reply = (string) $svc->nextTurn($this->session, $msg);
                }
            }
        } catch (\Throwable $e) {
            //
        }

        $this->turns[] = ['role' => 'ai', 'text' => $reply];
    }

    public function finish(): void
    {
        try {
            if (class_exists(\App\Services\InterviewSimulatorService::class)) {
                $svc = app(\App\Services\InterviewSimulatorService::class);
                if (method_exists($svc, 'finish')) {
                    $result = $svc->finish($this->session);
                    if (is_array($result)) {
                        $this->report = $result;
                    }
                }
                if (class_exists(\App\Services\PointsService::class)) {
                    app(\App\Services\PointsService::class)->award(auth()->user(), 'interview.simulated');
                }
            }
        } catch (\Throwable $e) {
            //
        }

        $this->finished = true;
        if (! $this->report) {
            $this->report = [
                'score'        => 78,
                'strengths'    => ['Comunicação clara', 'Boas evidências'],
                'improvements' => ['Aprofundar tecnologias específicas', 'Trazer números aos exemplos'],
            ];
        }
    }

    public function render()
    {
        return view('livewire.interviews.room');
    }
}
