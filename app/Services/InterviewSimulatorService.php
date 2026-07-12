<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InterviewSession;
use App\Models\InterviewTurn;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Simulador de entrevistas com IA: cria sessão, dispara perguntas e produz relatório final.
 */
class InterviewSimulatorService
{
    public function __construct(
        private AiService $ai,
        private PointsService $points,
    ) {
    }

    public function start(
        User $user,
        string $roleTitle,
        string $seniority,
        ?JobListing $job = null,
        string $mode = 'text',
    ): InterviewSession {
        return InterviewSession::create([
            'user_id'        => $user->id,
            'role_title'     => $roleTitle,
            'seniority'      => $seniority,
            'job_listing_id' => $job?->id,
            'mode'           => $mode,
            'status'         => 'in_progress',
            'started_at'     => now(),
        ]);
    }

    /**
     * Solicita à IA a próxima pergunta a partir do histórico da sessão.
     */
    public function nextQuestion(InterviewSession $session): InterviewTurn
    {
        $history = $session->turns()->orderBy('position')->get()
            ->map(fn (InterviewTurn $t) => [
                'role'    => $t->role === 'interviewer' ? 'assistant' : 'user',
                'content' => (string) $t->body,
            ])
            ->all();

        $context = "Você está entrevistando um(a) candidato(a) para {$session->role_title} "
            . "senioridade {$session->seniority}. Faça UMA pergunta por vez, "
            . "focando em experiências reais, comportamento e conhecimento técnico. "
            . "Se já houver histórico, aprofunde a resposta anterior.";

        $messages = array_merge(
            [['role' => 'system', 'content' => $context]],
            $history,
            [['role' => 'user', 'content' => 'Faça a próxima pergunta.']]
        );

        $body = $this->ai->chat($messages);
        $position = ((int) $session->turns()->max('position')) + 1;

        return InterviewTurn::create([
            'session_id' => $session->id,
            'role'       => 'interviewer',
            'body'       => $body ?: 'Fale sobre você.',
            'position'   => $position,
            'created_at' => now(),
        ]);
    }

    /**
     * Registra a resposta do candidato na sessão.
     */
    public function respond(InterviewSession $session, string $body, ?string $audioPath = null): InterviewTurn
    {
        $position = ((int) $session->turns()->max('position')) + 1;

        return InterviewTurn::create([
            'session_id' => $session->id,
            'role'       => 'candidate',
            'body'       => $body,
            'audio_path' => $audioPath,
            'position'   => $position,
            'created_at' => now(),
        ]);
    }

    /**
     * Encerra a sessão e pede relatório estruturado à IA.
     */
    public function finish(InterviewSession $session): InterviewSession
    {
        return DB::transaction(function () use ($session) {
            $turns = $session->turns()->orderBy('position')->get();
            $transcript = $turns->map(fn (InterviewTurn $t) => strtoupper($t->role) . ': ' . $t->body)
                ->implode("\n");

            $prompt = <<<TXT
Analise a entrevista abaixo (papel: {$session->role_title}, senioridade: {$session->seniority}).
Retorne JSON estrito:
{
  "overall_score": <0-100>,
  "scores": {"comunicacao": <0-100>, "tecnico": <0-100>, "comportamental": <0-100>, "clareza": <0-100>},
  "feedback": "texto em markdown com pontos fortes, a melhorar e recomendação"
}
Transcrição:
"""
{$transcript}
"""
TXT;

            $raw = $this->ai->chat([['role' => 'user', 'content' => $prompt]]);
            $data = json_decode(trim(preg_replace('/^```(?:json)?|```$/i', '', $raw)), true) ?: [];

            $session->overall_score = (int) ($data['overall_score'] ?? 0);
            $session->scores        = (array) ($data['scores'] ?? []);
            $session->feedback      = (string) ($data['feedback'] ?? '');
            $session->status        = 'finished';
            $session->finished_at   = now();
            $session->save();

            // XP: sempre entrega XP por simular; bônus se score alto
            $user = $session->user;
            if ($user) {
                $this->points->award($user, 'interview.simulated', $session, 'interview.simulated:' . $session->id);
                if ($session->overall_score >= 80) {
                    $this->points->award($user, 'interview.high_score', $session, 'interview.high_score:' . $session->id);
                }
            }

            return $session->fresh(['turns']);
        });
    }
}
