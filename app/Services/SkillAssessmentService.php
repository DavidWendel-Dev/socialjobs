<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SkillAssessment;
use App\Models\SkillAssessmentAttempt;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Serviço de correção e listagem dos "Skill Assessments".
 *
 * - submit(): recebe as respostas do usuário, calcula score, marca passed
 *   se >= passing_score, concede XP na primeira aprovação e retorna o Attempt.
 * - bestScoresFor(): retorna as melhores pontuações do usuário para exibir
 *   no perfil e Currículo Digital.
 */
class SkillAssessmentService
{
    public function __construct(private PointsService $points)
    {
    }

    /**
     * Corrige e persiste uma tentativa.
     *
     * @param array<int,int|null> $answers ["questionId" => índiceEscolhido|null]
     * @param array{
     *   tab_leaves?:int,
     *   copy_attempts?:int,
     *   devtools_opens?:int,
     *   auto_terminated?:bool
     * } $integrity Sinais de integridade do frontend (opcional).
     */
    public function submit(
        User $user,
        SkillAssessment $assessment,
        array $answers,
        int $durationSeconds = 0,
        array $integrity = []
    ): SkillAssessmentAttempt {
        return DB::transaction(function () use ($user, $assessment, $answers, $durationSeconds, $integrity) {
            $questions = $assessment->questions()->get();
            $total     = $questions->count();
            $correct   = 0;

            foreach ($questions as $q) {
                $chosen = $answers[$q->id] ?? null;
                if ($chosen !== null && (int) $chosen === (int) $q->correct_index) {
                    $correct++;
                }
            }

            $score  = $total > 0 ? (int) round(($correct / $total) * 100) : 0;
            $passed = $score >= (int) $assessment->passing_score;

            // ============================================================
            // Análise de integridade — decide status "clean/suspicious/terminated"
            // ============================================================
            $tabLeaves     = (int) ($integrity['tab_leaves'] ?? 0);
            $copyAttempts  = (int) ($integrity['copy_attempts'] ?? 0);
            $devtoolsOpens = (int) ($integrity['devtools_opens'] ?? 0);
            $autoTerm      = (bool) ($integrity['auto_terminated'] ?? false);

            if ($autoTerm) {
                // Sistema finalizou por violação — o teste é reprovado e marcado
                $integrityStatus = 'auto_terminated';
                $passed = false;
                $score  = min($score, (int) $assessment->passing_score - 1);
            } elseif ($tabLeaves >= 2 || $copyAttempts >= 5 || $devtoolsOpens >= 1) {
                // Comportamento suspeito — passa se acertou, mas fica com "asterisco"
                $integrityStatus = 'suspicious';
            } else {
                $integrityStatus = 'clean';
            }

            $attempt = SkillAssessmentAttempt::create([
                'user_id'             => $user->id,
                'skill_assessment_id' => $assessment->id,
                'score'               => $score,
                'passed'              => $passed,
                'answers'             => $answers,
                'duration_seconds'    => $durationSeconds,
                'tab_leaves'          => $tabLeaves,
                'copy_attempts'       => $copyAttempts,
                'devtools_opens'      => $devtoolsOpens,
                'integrity_status'    => $integrityStatus,
                'started_at'          => now()->subSeconds($durationSeconds),
                'finished_at'         => now(),
            ]);

            // Concede XP apenas na primeira aprovação nesse teste com integridade limpa
            // (idempotência via uniqueKey: skill.passed:{userId}:{assessmentId}).
            if ($passed && $integrityStatus !== 'auto_terminated') {
                $this->points->award(
                    $user,
                    'skill.passed',
                    $assessment,
                    'skill.passed:' . $user->id . ':' . $assessment->id
                );
            }

            return $attempt;
        });
    }

    /**
     * Submissão "crua" — usada quando as questões vêm da IA em runtime.
     * O score já vem calculado pelo componente Livewire (que tem as questões
     * em memória), e aqui aplicamos apenas as regras de integridade e persistência.
     *
     * @param array<int,int> $answers  [questionId (pode ser negativo) => optionIndex]
     * @param int $score               Score já calculado (0..100)
     * @param int $durationSeconds
     * @param array{
     *   tab_leaves?:int, copy_attempts?:int, devtools_opens?:int,
     *   auto_terminated?:bool
     * } $integrity
     */
    public function submitRaw(
        User $user,
        SkillAssessment $assessment,
        array $answers,
        int $score,
        int $durationSeconds = 0,
        array $integrity = []
    ): SkillAssessmentAttempt {
        return DB::transaction(function () use ($user, $assessment, $answers, $score, $durationSeconds, $integrity) {
            $passed = $score >= (int) $assessment->passing_score;

            $tabLeaves     = (int) ($integrity['tab_leaves'] ?? 0);
            $copyAttempts  = (int) ($integrity['copy_attempts'] ?? 0);
            $devtoolsOpens = (int) ($integrity['devtools_opens'] ?? 0);
            $autoTerm      = (bool) ($integrity['auto_terminated'] ?? false);

            if ($autoTerm) {
                $integrityStatus = 'auto_terminated';
                $passed = false;
                $score  = min($score, (int) $assessment->passing_score - 1);
            } elseif ($tabLeaves >= 2 || $copyAttempts >= 5 || $devtoolsOpens >= 1) {
                $integrityStatus = 'suspicious';
            } else {
                $integrityStatus = 'clean';
            }

            $attempt = SkillAssessmentAttempt::create([
                'user_id'             => $user->id,
                'skill_assessment_id' => $assessment->id,
                'score'               => $score,
                'passed'              => $passed,
                'answers'             => $answers,
                'duration_seconds'    => $durationSeconds,
                'tab_leaves'          => $tabLeaves,
                'copy_attempts'       => $copyAttempts,
                'devtools_opens'      => $devtoolsOpens,
                'integrity_status'    => $integrityStatus,
                'started_at'          => now()->subSeconds($durationSeconds),
                'finished_at'         => now(),
            ]);

            if ($passed && $integrityStatus !== 'auto_terminated') {
                $this->points->award(
                    $user,
                    'skill.passed',
                    $assessment,
                    'skill.passed:' . $user->id . ':' . $assessment->id
                );
            }

            return $attempt;
        });
    }

    /**
     * Melhor score do usuário em cada assessment em que ele passou (>= passing_score).
     *
     * Retorna: Collection<int,array{
     *   assessment: SkillAssessment,
     *   best_score: int,
     *   passed_at:  \Illuminate\Support\Carbon,
     *   attempts:   int,
     * }>
     */
    public function bestScoresFor(User $user): Collection
    {
        return SkillAssessmentAttempt::query()
            ->with('assessment')
            ->where('user_id', $user->id)
            ->where('passed', true)
            ->orderByDesc('score')
            ->get()
            ->groupBy('skill_assessment_id')
            ->map(function ($group) {
                /** @var SkillAssessmentAttempt $best */
                $best = $group->sortByDesc('score')->first();
                return [
                    'assessment' => $best->assessment,
                    'best_score' => (int) $best->score,
                    'passed_at'  => $best->finished_at ?? $best->created_at,
                    'attempts'   => $group->count(),
                ];
            })
            ->values()
            ->sortByDesc('best_score')
            ->values();
    }

    /**
     * Última tentativa (independente de ter passado) — útil para o cooldown
     * de 24h antes de refazer.
     */
    public function canRetry(User $user, SkillAssessment $assessment): bool
    {
        $last = SkillAssessmentAttempt::where('user_id', $user->id)
            ->where('skill_assessment_id', $assessment->id)
            ->latest()
            ->first();

        if (! $last) {
            return true;
        }

        // Se já passou, pode refazer sempre (para melhorar o score).
        if ($last->passed) {
            return true;
        }

        // Reprovou → cooldown de 24h.
        return $last->created_at->lt(now()->subHours(24));
    }
}
