<?php

declare(strict_types=1);

namespace App\Livewire\Jobs;

use App\Models\Application;
use App\Models\AssessmentInvitation;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\SkillAssessment;
use App\Models\SkillAssessmentAttempt;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kanban de vagas · SocialJobs')]
class Kanban extends Component
{
    /** @var array<string,string> */
    public array $columns = [
        'received'  => 'Recebido',
        'reviewing' => 'Em análise',
        'interview' => 'Entrevista',
        'offer'     => 'Oferta',
        'hired'     => 'Contratado',
        'rejected'  => 'Rejeitado',
    ];

    /* --------- Filtros --------- */
    public string $search        = '';
    public ?int   $jobFilter     = null;
    public string $skillFilter   = '';
    public bool   $onlyPassed    = false;
    public string $orderBy       = 'recent'; // recent | score
    public string $mobileColumn  = 'received';

    /* --------- Modal --------- */
    public bool $showModal          = false;
    public ?int $selectedId         = null;
    public string $activeTab        = 'about';
    public string $rejectMessage    = '';
    public bool   $showRejectPrompt = false;
    public string $noteDraft        = '';

    /* --------- Estado das colunas próximas --------- */
    /** @var array<string,string> */
    protected array $nextStatus = [
        'received'  => 'reviewing',
        'reviewing' => 'interview',
        'interview' => 'offer',
        'offer'     => 'hired',
    ];

    /* ============================================================
     * Helpers
     * ============================================================ */
    protected function companyProfileId(): ?int
    {
        return auth()->user()?->companyProfile?->id;
    }

    protected function findOwnedApplication(int $applicationId): ?Application
    {
        $companyProfileId = $this->companyProfileId();
        if (! $companyProfileId) return null;

        return Application::query()
            ->whereHas('jobListing', fn ($q) => $q->where('company_profile_id', $companyProfileId))
            ->where('id', $applicationId)
            ->first();
    }

    /* ============================================================
     * Ações de status
     * ============================================================ */
    public function updateStatus(int $applicationId, string $status): void
    {
        if (! array_key_exists($status, $this->columns)) return;

        $app = $this->findOwnedApplication($applicationId);
        if (! $app) return;

        $app->update(['status' => $status]);
    }

    public function moveNext(int $applicationId): void
    {
        $app = $this->findOwnedApplication($applicationId);
        if (! $app) return;

        $next = $this->nextStatus[$app->status] ?? null;
        if ($next) {
            $app->update(['status' => $next]);
        }
    }

    public function quickReject(int $applicationId): void
    {
        $app = $this->findOwnedApplication($applicationId);
        if (! $app) return;

        $app->update(['status' => 'rejected']);
    }

    /* ============================================================
     * Modal
     * ============================================================ */
    public function openDetails(int $applicationId): void
    {
        $app = $this->findOwnedApplication($applicationId);
        if (! $app) return;

        $this->selectedId       = $app->id;
        $this->activeTab        = 'about';
        $this->rejectMessage    = '';
        $this->showRejectPrompt = false;
        $this->noteDraft        = (string) ($app->internal_note ?? '');
        $this->showModal        = true;
    }

    public function closeModal(): void
    {
        $this->showModal        = false;
        $this->selectedId       = null;
        $this->showRejectPrompt = false;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function clearFilters(): void
    {
        $this->search      = '';
        $this->jobFilter   = null;
        $this->skillFilter = '';
        $this->onlyPassed  = false;
        $this->orderBy     = 'recent';
    }

    /* ============================================================
     * Nota interna (auto-save via debounce)
     * ============================================================ */
    public function updatedNoteDraft(string $value): void
    {
        if (! $this->selectedId) return;

        $app = $this->findOwnedApplication($this->selectedId);
        if (! $app) return;

        $app->update(['internal_note' => $value !== '' ? $value : null]);
        $this->dispatch('note-saved');
    }

    /* ============================================================
     * Ações do modal
     * ============================================================ */
    public function actMoveTo(string $status): void
    {
        if (! $this->selectedId) return;
        if (! array_key_exists($status, $this->columns)) return;

        $app = $this->findOwnedApplication($this->selectedId);
        if (! $app) return;

        $app->update(['status' => $status]);
    }

    public function toggleRejectPrompt(): void
    {
        $this->showRejectPrompt = ! $this->showRejectPrompt;
    }

    public function confirmReject(): void
    {
        if (! $this->selectedId) return;
        $app = $this->findOwnedApplication($this->selectedId);
        if (! $app) return;

        $app->update(['status' => 'rejected']);

        // Se digitou mensagem, cria conversa/mensagem
        $body = trim($this->rejectMessage);
        if ($body !== '') {
            try {
                $companyUserId = auth()->id();
                $candidateId   = (int) $app->user_id;

                DB::transaction(function () use ($companyUserId, $candidateId, $body) {
                    // Tenta reusar conversa direta existente
                    $conversation = Conversation::query()
                        ->where('type', 'direct')
                        ->whereHas('participants', fn ($q) => $q->where('users.id', $companyUserId))
                        ->whereHas('participants', fn ($q) => $q->where('users.id', $candidateId))
                        ->first();

                    if (! $conversation) {
                        $conversation = Conversation::create(['type' => 'direct']);
                        $conversation->participants()->attach([$companyUserId, $candidateId]);
                    }

                    Message::create([
                        'conversation_id' => $conversation->id,
                        'user_id'        => $companyUserId,
                        'body'           => $body,
                    ]);
                });
            } catch (\Throwable $e) {
                // Fail-safe: não trava a rejeição por causa da mensagem
            }
        }

        $this->rejectMessage    = '';
        $this->showRejectPrompt = false;
        $this->closeModal();
    }

    public function sendAssessment(int $assessmentId): void
    {
        if (! $this->selectedId) return;

        $app = $this->findOwnedApplication($this->selectedId);
        if (! $app) return;

        $companyProfileId = $this->companyProfileId();
        if (! $companyProfileId) return;

        $assessment = SkillAssessment::query()
            ->where('id', $assessmentId)
            ->where('owner_type', 'company')
            ->where('company_profile_id', $companyProfileId)
            ->first();

        if (! $assessment) return;

        AssessmentInvitation::create([
            'skill_assessment_id' => $assessment->id,
            'company_profile_id'  => $companyProfileId,
            'job_application_id'  => $app->id,
            'candidate_user_id'   => $app->user_id,
            'candidate_email'     => $app->user?->email,
            'expires_at'          => now()->addDays(14),
        ]);

        session()->flash('status', 'Convite de teste enviado ao candidato.');
        $this->dispatch('invite-sent');
    }

    /* ============================================================
     * Render
     * ============================================================ */
    public function render()
    {
        $companyProfileId = $this->companyProfileId();

        $groups = [];
        foreach ($this->columns as $key => $label) {
            $groups[$key] = collect();
        }

        $jobs        = collect();
        $companyTests = collect();
        $selected    = null;

        if ($companyProfileId) {
            $jobs = \App\Models\JobListing::query()
                ->where('company_profile_id', $companyProfileId)
                ->orderBy('title')
                ->get(['id', 'title', 'status']);

            $companyTests = SkillAssessment::query()
                ->where('owner_type', 'company')
                ->where('company_profile_id', $companyProfileId)
                ->where('is_active', true)
                ->orderBy('title')
                ->get(['id', 'title']);

            $q = Application::query()
                ->whereHas('jobListing', fn ($q) => $q->where('company_profile_id', $companyProfileId))
                ->with([
                    'user:id,name,username,email,avatar_path,headline,location',
                    'user.candidateProfile:id,user_id,bio',
                    'user.candidateProfile.skills:id,name,slug',
                    'jobListing:id,title,company_profile_id',
                ])
                ->addSelect([
                    'passed_attempts_count' => \App\Models\SkillAssessmentAttempt::query()
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('skill_assessment_attempts.user_id', 'applications.user_id')
                        ->where('passed', true),
                    'invited_count' => AssessmentInvitation::query()
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('assessment_invitations.candidate_user_id', 'applications.user_id')
                        ->where('company_profile_id', $companyProfileId),
                ]);

            // Filtros
            if ($this->jobFilter) {
                $q->where('job_listing_id', $this->jobFilter);
            }

            if ($this->search !== '') {
                $term = '%' . $this->search . '%';
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', $term));
            }

            if ($this->skillFilter !== '') {
                $term = '%' . $this->skillFilter . '%';
                $q->whereHas('user.candidateProfile.skills', fn ($s) => $s->where('name', 'like', $term));
            }

            if ($this->onlyPassed) {
                $q->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('skill_assessment_attempts')
                        ->whereColumn('skill_assessment_attempts.user_id', 'applications.user_id')
                        ->where('passed', true);
                });
            }

            if ($this->orderBy === 'score') {
                // média de score dos attempts do user via subquery
                $q->addSelect([
                    'avg_score' => \App\Models\SkillAssessmentAttempt::query()
                        ->selectRaw('AVG(score)')
                        ->whereColumn('skill_assessment_attempts.user_id', 'applications.user_id'),
                ])->orderByRaw('(SELECT AVG(score) FROM skill_assessment_attempts WHERE skill_assessment_attempts.user_id = applications.user_id) IS NULL, (SELECT AVG(score) FROM skill_assessment_attempts WHERE skill_assessment_attempts.user_id = applications.user_id) DESC');
            } else {
                $q->latest();
            }

            $apps = $q->take(300)->get();

            foreach ($apps as $a) {
                $status = $a->status ?? 'received';
                if (isset($groups[$status])) {
                    $groups[$status]->push($a);
                }
            }

            // Se modal aberto, faz eager-load completo do candidato selecionado
            if ($this->showModal && $this->selectedId) {
                $selected = Application::query()
                    ->whereHas('jobListing', fn ($q) => $q->where('company_profile_id', $companyProfileId))
                    ->with([
                        'user',
                        'user.candidateProfile.skills',
                        'user.candidateProfile.experiences' => fn ($q) => $q->orderByDesc('current')->orderByDesc('start_date'),
                        'user.candidateProfile.educations'  => fn ($q) => $q->orderByDesc('start_date'),
                        'user.candidateProfile.portfolioItems',
                        'jobListing',
                    ])
                    ->find($this->selectedId);

                if ($selected) {
                    // Attempts
                    $selected->setRelation(
                        'attempts',
                        SkillAssessmentAttempt::query()
                            ->with('assessment:id,title,passing_score')
                            ->where('user_id', $selected->user_id)
                            ->whereNotNull('finished_at')
                            ->orderByDesc('finished_at')
                            ->get()
                    );

                    // Endorsements agregados por skill_id
                    $endorsementsCounts = DB::table('endorsements')
                        ->select('skill_id', DB::raw('COUNT(*) as total'))
                        ->where('endorsed_user_id', $selected->user_id)
                        ->groupBy('skill_id')
                        ->pluck('total', 'skill_id');
                    $selected->setAttribute('endorsements_counts', $endorsementsCounts);

                    // Marca "veio de convite"?
                    $invitedCount = AssessmentInvitation::query()
                        ->where('company_profile_id', $companyProfileId)
                        ->where('candidate_user_id', $selected->user_id)
                        ->count();
                    $selected->setAttribute('was_invited', $invitedCount > 0);
                }
            }
        }

        return view('livewire.jobs.kanban', [
            'groups'       => $groups,
            'jobs'         => $jobs,
            'companyTests' => $companyTests,
            'selected'     => $selected,
            'nextStatus'   => $this->nextStatus,
        ]);
    }
}
