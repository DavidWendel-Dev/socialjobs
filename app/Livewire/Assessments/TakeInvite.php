<?php

declare(strict_types=1);

namespace App\Livewire\Assessments;

use App\Models\AssessmentInvitation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Ponto de entrada para candidatos que receberam um convite (link com token).
 *
 * Fluxo:
 *  1. Valida o token
 *  2. Se ainda não completado, marca 'opened' e armazena invitation_id na sessão
 *  3. Redireciona para a página normal de execução do teste
 *     (SkillAssessments\Take), que já lida com a persistência do Attempt.
 *  4. Quando o Attempt é salvo lá, o hook verifica session('invitation_id')
 *     e amarra o attempt/marca como 'completed' na invitation.
 */
#[Layout('layouts.app')]
#[Title('Convite de teste · SocialJobs')]
class TakeInvite extends Component
{
    public string $token = '';
    public ?AssessmentInvitation $invitation = null;
    public string $errorState = ''; // '', 'not_found', 'expired', 'completed', 'needs_login'

    public function mount(string $token): void
    {
        $this->token      = $token;
        $this->invitation = AssessmentInvitation::query()
            ->with(['assessment', 'companyProfile'])
            ->where('token', $token)
            ->first();

        if (! $this->invitation) {
            $this->errorState = 'not_found';
            return;
        }

        if ($this->invitation->isExpired()) {
            if ($this->invitation->status !== 'expired') {
                $this->invitation->update(['status' => 'expired']);
            }
            $this->errorState = 'expired';
            return;
        }

        if ($this->invitation->status === 'completed') {
            $this->errorState = 'completed';
            return;
        }

        if (! auth()->check()) {
            // Guarda intended URL para redirecionar de volta após login/registro
            session(['url.intended' => route('assessments.take-invite', ['token' => $token])]);
            session(['pending_invitation_token' => $token]);
            $this->errorState = 'needs_login';
            return;
        }

        // Usuário logado — marca como "aberto" e amarra ao user
        $updates = ['status' => 'opened'];
        if (! $this->invitation->opened_at) {
            $updates['opened_at'] = now();
        }
        if (! $this->invitation->candidate_user_id) {
            $updates['candidate_user_id'] = auth()->id();
        }
        $this->invitation->update($updates);

        // Session flag para o Take amarrar o attempt na invitation ao finalizar
        session([
            'invitation_id'    => $this->invitation->id,
            'invitation_token' => $this->invitation->token,
        ]);

        // Redireciona para o Take normal do assessment
        $this->redirectRoute(
            'skill-assessments.take',
            ['slug' => $this->invitation->assessment->slug],
            navigate: false
        );
    }

    public function render(): View
    {
        return view('livewire.assessments.take-invite');
    }
}
