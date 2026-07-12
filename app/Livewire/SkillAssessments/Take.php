<?php

declare(strict_types=1);

namespace App\Livewire\SkillAssessments;

use App\Models\SkillAssessment;
use App\Services\GroqQuestionGenerator;
use App\Services\SkillAssessmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Realizar um teste de proficiência.
 * URL: /skill-assessments/{slug}
 *
 * Questões são GERADAS EM TEMPO REAL pela Groq quando o candidato inicia
 * o teste. As perguntas ficam apenas na memória do componente Livewire
 * (não no banco). Ao finalizar, só o {@see SkillAssessmentAttempt} com
 * as respostas do usuário é persistido.
 *
 * Anti-cola:
 *  - Cada tentativa tem 20 perguntas NOVAS geradas em runtime
 *  - Timer + contador de saídas de aba + bloqueio de cópia (JS)
 *  - Auto-finalização em 3 saídas de aba
 */
#[Layout('layouts.app')]
#[Title('Teste · SocialJobs')]
class Take extends Component
{
    public SkillAssessment $assessment;

    /** 'intro' | 'loading' | 'running' | 'result' */
    public string $state = 'intro';

    /** Índice da questão em andamento (0-based). */
    public int $currentIndex = 0;

    /**
     * As 20 questões dessa sessão (geradas por IA), armazenadas na memória
     * do componente Livewire. Cada item é um array serializável com:
     *   id (negativo), statement, options[], correct_index, explanation.
     */
    public array $questions = [];

    /** Respostas: [questionId => displayedIndex]. */
    public array $answers = [];

    /** Timestamp Unix (ms) do início — usado para calcular duração. */
    public ?int $startedAtMs = null;

    /** Sinais forenses vindos do JS. */
    public int $tabLeaves = 0;
    public int $copyAttempts = 0;
    public int $devtoolsOpens = 0;

    /** Se true, o próximo finish() vai marcar como auto_terminated. */
    public bool $autoTerminated = false;

    /** Se true, houve falha ao gerar via IA e usamos o fallback do banco. */
    public bool $usedFallback = false;

    /** Após submissão. */
    public ?int $lastAttemptId = null;
    public ?int $lastScore = null;
    public bool $lastPassed = false;
    public string $lastIntegrity = 'clean';

    public function mount(string $slug): void
    {
        $this->assessment = SkillAssessment::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        if (! auth()->check()) {
            abort(302, '', ['Location' => route('login')]);
        }
    }

    /**
     * Inicia o teste — pede 20 questões novas para a Groq e persiste
     * na memória do componente. Enquanto gera, o state fica em "loading".
     */
    public function start(): void
    {
        $this->state = 'loading';
        // Dispara o carregamento efetivo no próximo request para a UI mostrar spinner
        $this->dispatch('start-generation');
    }

    /**
     * Chamado logo após o `start()` — faz a chamada síncrona à IA.
     * Se falhar, cai no fallback do banco.
     */
    public function generateQuestions(): void
    {
        $generator = app(GroqQuestionGenerator::class);
        $qs = $generator->generate($this->assessment, 20);

        if (empty($qs)) {
            // Sem IA e sem banco — mostra erro
            $this->state = 'intro';
            session()->flash('error', 'Não foi possível gerar as questões agora. Tente novamente em instantes.');
            return;
        }

        // Se algum ID for positivo, é fallback do banco
        $this->usedFallback = collect($qs)->contains(fn ($q) => $q->id > 0);

        // Serializa como array puro (Livewire não persiste bem stdClass)
        $this->questions = array_values(array_map(fn ($q) => [
            'id'            => (int) $q->id,
            'statement'     => (string) $q->statement,
            'options'       => array_values($q->options),
            'correct_index' => (int) $q->correct_index,
            'explanation'   => (string) $q->explanation,
        ], $qs));

        $this->state         = 'running';
        $this->currentIndex  = 0;
        $this->answers       = [];
        $this->tabLeaves     = 0;
        $this->copyAttempts  = 0;
        $this->devtoolsOpens = 0;
        $this->autoTerminated = false;
        $this->startedAtMs   = (int) (microtime(true) * 1000);
    }

    /** Registra a resposta e avança. */
    public function answerAndNext(int $questionId, int $chosenIndex): void
    {
        $this->answers[$questionId] = $chosenIndex;

        $total = count($this->questions);
        if ($this->currentIndex < $total - 1) {
            $this->currentIndex++;
        }
    }

    public function previous(): void
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
        }
    }

    public function registerTabLeave(): void
    {
        $this->tabLeaves++;
        if ($this->tabLeaves >= 3) {
            $this->autoTerminated = true;
            $this->finish();
        }
    }

    public function registerCopyAttempt(): void
    {
        $this->copyAttempts++;
    }

    public function registerDevtoolsOpen(): void
    {
        $this->devtoolsOpens++;
        if ($this->devtoolsOpens >= 2) {
            $this->autoTerminated = true;
            $this->finish();
        }
    }

    /**
     * Finaliza: calcula score comparando com as questões em memória,
     * persiste o attempt no banco (sem persistir as perguntas).
     */
    public function finish(): void
    {
        $durationSeconds = $this->startedAtMs
            ? (int) round(((microtime(true) * 1000) - $this->startedAtMs) / 1000)
            : 0;

        $total   = count($this->questions);
        $correct = 0;
        foreach ($this->questions as $q) {
            $chosen = $this->answers[$q['id']] ?? null;
            if ($chosen !== null && (int) $chosen === (int) $q['correct_index']) {
                $correct++;
            }
        }
        $score  = $total > 0 ? (int) round(($correct / $total) * 100) : 0;

        // Delega ao service para aplicar regras de integridade e criar o Attempt.
        // Passamos as respostas "cruas" (o service aceita array de answers) e o
        // score/passed já calculado através de um método auxiliar.
        $service = app(SkillAssessmentService::class);
        $attempt = $service->submitRaw(
            auth()->user(),
            $this->assessment,
            $this->answers,
            $score,
            $durationSeconds,
            [
                'tab_leaves'      => $this->tabLeaves,
                'copy_attempts'   => $this->copyAttempts,
                'devtools_opens'  => $this->devtoolsOpens,
                'auto_terminated' => $this->autoTerminated,
            ]
        );

        $this->lastAttemptId = $attempt->id;
        $this->lastScore     = (int) $attempt->score;
        $this->lastPassed    = (bool) $attempt->passed;
        $this->lastIntegrity = (string) $attempt->integrity_status;
        $this->state         = 'result';

        // Se o candidato veio de um convite de empresa (rota /take/{token}),
        // amarra este attempt à AssessmentInvitation e marca como concluída.
        $invitationId = session('invitation_id');
        if ($invitationId) {
            $inv = \App\Models\AssessmentInvitation::query()
                ->where('id', $invitationId)
                ->where('skill_assessment_id', $this->assessment->id)
                ->first();

            if ($inv) {
                $inv->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                    'attempt_id'   => $attempt->id,
                    'candidate_user_id' => $inv->candidate_user_id ?? auth()->id(),
                ]);
            }
            session()->forget(['invitation_id', 'invitation_token']);
        }
    }

    /** Refazer — gera novas questões. */
    public function restart(): void
    {
        $this->state          = 'intro';
        $this->currentIndex   = 0;
        $this->questions      = [];
        $this->answers        = [];
        $this->startedAtMs    = null;
        $this->tabLeaves      = 0;
        $this->copyAttempts   = 0;
        $this->devtoolsOpens  = 0;
        $this->autoTerminated = false;
        $this->usedFallback   = false;
        $this->lastAttemptId  = null;
        $this->lastScore      = null;
        $this->lastPassed     = false;
        $this->lastIntegrity  = 'clean';
    }

    /** Retorna as questões como Collection para o Blade. */
    public function questionsCollection(): Collection
    {
        return collect($this->questions)->map(fn ($q) => (object) $q);
    }

    public function render(): View
    {
        $questions = $this->questionsCollection();
        $current   = $questions[$this->currentIndex] ?? null;
        $answered  = count($this->answers);
        $total     = $questions->count();

        return view('livewire.skill-assessments.take', [
            'questions'         => $questions,
            'current'           => $current,
            'currentDisplayed'  => $current
                ? ($this->answers[$current->id] ?? null)
                : null,
            'answered'          => $answered,
            'total'             => $total,
            'bestBefore'        => $this->state === 'result' && auth()->check()
                ? $this->assessment->bestScoreFor(auth()->user())
                : null,
            'displayedAnswers'  => $this->answers,
        ]);
    }
}
