<?php

declare(strict_types=1);

namespace App\Livewire\Company\Assessments;

use App\Models\AssessmentInvitation;
use App\Models\SkillAssessment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Resultados do teste · SocialJobs')]
class Results extends Component
{
    public SkillAssessment $assessment;

    public bool $showInviteModal = false;

    #[Validate('required|email|max:191')]
    public string $inviteEmail = '';

    public ?string $lastInviteUrl = null;

    public function mount(SkillAssessment $assessment): void
    {
        $cp = auth()->user()?->companyProfile;
        abort_unless($cp !== null, 403);
        abort_unless(
            $assessment->owner_type === 'company'
                && (int) $assessment->company_profile_id === (int) $cp->id,
            403
        );

        $this->assessment = $assessment;
    }

    public function openInviteModal(): void
    {
        $this->reset(['inviteEmail', 'lastInviteUrl']);
        $this->resetErrorBag();
        $this->showInviteModal = true;
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
    }

    public function sendInvite(): void
    {
        $this->validate();

        $cp = auth()->user()?->companyProfile;
        abort_unless($cp !== null, 403);

        // Se o email já pertence a um usuário, referencia
        $candidateUser = \App\Models\User::query()
            ->where('email', $this->inviteEmail)
            ->first();

        $invitation = AssessmentInvitation::create([
            'skill_assessment_id' => $this->assessment->id,
            'company_profile_id'  => $cp->id,
            'candidate_user_id'   => $candidateUser?->id,
            'candidate_email'     => $this->inviteEmail,
            'expires_at'          => now()->addDays(14),
        ]);

        $this->lastInviteUrl = $invitation->invitationUrl();
        $this->inviteEmail   = '';
        session()->flash('status', 'Convite criado! Copie o link e envie ao candidato.');
    }

    public function render(): View
    {
        $invitations = $this->assessment
            ->invitations()
            ->with(['candidate', 'attempt'])
            ->latest()
            ->get();

        $total     = $invitations->count();
        $completed = $invitations->where('status', 'completed')->count();

        $attempts  = $this->assessment->attempts()->get();
        $avgScore  = $attempts->count() ? (int) round($attempts->avg('score')) : null;
        $passed    = $attempts->where('passed', true)->count();
        $failed    = $attempts->count() - $passed;

        return view('livewire.company.assessments.results', [
            'invitations' => $invitations,
            'stats'       => [
                'total'     => $total,
                'completed' => $completed,
                'avg_score' => $avgScore,
                'passed'    => $passed,
                'failed'    => $failed,
                'attempts'  => $attempts->count(),
            ],
        ]);
    }
}
