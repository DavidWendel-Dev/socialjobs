<?php

declare(strict_types=1);

namespace App\Livewire\Company;

use App\Models\AssessmentInvitation;
use App\Models\Skill;
use App\Models\SkillAssessment;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Banco de Talentos — busca avançada de candidatos para empresas.
 *
 * Empresa filtra usuários candidatos por skills, localização,
 * "aberto a trabalhar", currículo, testes aprovados e experiência.
 * A partir do resultado ela pode ver o perfil, mandar mensagem
 * (DM via ChatService) ou convidar para um teste customizado.
 */
#[Layout('layouts.app')]
#[Title('Banco de Talentos · SocialJobs')]
class Talents extends Component
{
    use WithPagination;

    /** Texto livre — busca em name/username/headline. */
    public string $search = '';

    /** Skills selecionadas (array de nomes). */
    public array $skillNames = [];

    /** Busca de skill no autocomplete. */
    public string $skillQuery = '';

    /** Filtro de cidade (parte de users.location). */
    public string $city = '';

    /** Filtro de UF — 2 letras. */
    public string $uf = '';

    /** Somente candidatos com open_to_work=true. */
    public bool $openToWork = false;

    /** Somente candidatos com CV completo (bio + resume_path). */
    public bool $hasResume = false;

    /** Somente candidatos com ao menos 1 teste aprovado. */
    public bool $hasAssessments = false;

    /** Mínimo de testes aprovados. */
    public int $minAssessmentsPassed = 0;

    /** Faixa de experiência: any | junior | pleno | senior. */
    public string $experienceYears = 'any';

    /** Ordenação. */
    public string $sortBy = 'recent';

    /* -------- Estado do modal "Convidar para teste" -------- */
    public bool $showInviteModal = false;

    public ?int $inviteUserId = null;

    public ?int $inviteAssessmentId = null;

    public string $inviteEmail = '';

    /* ========================================================
     |  Ciclo de vida
     |======================================================== */

    public function updating($name): void
    {
        // Reset da paginação em qualquer filtro
        $filterFields = [
            'search', 'skillNames', 'city', 'uf', 'openToWork',
            'hasResume', 'hasAssessments', 'minAssessmentsPassed',
            'experienceYears', 'sortBy',
        ];
        if (in_array($name, $filterFields, true)) {
            $this->resetPage();
        }
    }

    /* ========================================================
     |  Ações
     |======================================================== */

    /**
     * Adiciona ou remove uma skill do filtro (case insensitive).
     */
    public function toggleSkill(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            return;
        }

        $lower = mb_strtolower($name);
        $normalized = collect($this->skillNames)
            ->map(fn ($s) => mb_strtolower((string) $s))
            ->all();

        $idx = array_search($lower, $normalized, true);
        if ($idx === false) {
            $this->skillNames[] = $name;
        } else {
            unset($this->skillNames[$idx]);
            $this->skillNames = array_values($this->skillNames);
        }

        $this->skillQuery = '';
        $this->resetPage();
    }

    /**
     * Limpa todos os filtros de busca.
     */
    public function resetFilters(): void
    {
        $this->reset([
            'search', 'skillNames', 'skillQuery', 'city', 'uf',
            'openToWork', 'hasResume', 'hasAssessments',
            'minAssessmentsPassed', 'experienceYears', 'sortBy',
        ]);
        $this->sortBy = 'recent';
        $this->experienceYears = 'any';
        $this->resetPage();
    }

    /**
     * Abre (ou cria) DM com o candidato e redireciona para a thread.
     */
    public function sendMessage(int $userId): void
    {
        $me = auth()->user();
        if (! $me || $me->id === $userId) {
            return;
        }

        $other = User::find($userId);
        if (! $other) {
            return;
        }

        try {
            $conversation = app(ChatService::class)->findOrCreateDm($me, $other);
            $this->redirectRoute('messages.show', ['conversation' => $conversation->id], navigate: false);
        } catch (\Throwable $e) {
            session()->flash('talent_open_dm_user', $userId);
            $this->redirectRoute('messages.index', navigate: false);
        }
    }

    /**
     * Abre modal para convidar o candidato a fazer um teste da empresa.
     */
    public function inviteToTest(int $userId): void
    {
        $user = User::find($userId);
        if (! $user) {
            return;
        }

        $this->inviteUserId = $user->id;
        $this->inviteEmail = (string) ($user->email ?? '');
        $this->inviteAssessmentId = null;
        $this->showInviteModal = true;
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
        $this->inviteUserId = null;
        $this->inviteAssessmentId = null;
        $this->inviteEmail = '';
    }

    /**
     * Envia efetivamente o convite (cria AssessmentInvitation).
     */
    public function sendInvite(): void
    {
        $cp = auth()->user()?->companyProfile;
        if (! $cp) {
            session()->flash('status', 'Perfil de empresa não encontrado.');

            return;
        }

        $this->validate([
            'inviteUserId'       => 'required|integer|exists:users,id',
            'inviteAssessmentId' => 'required|integer|exists:skill_assessments,id',
            'inviteEmail'        => 'required|email',
        ], [], [
            'inviteAssessmentId' => 'teste',
            'inviteEmail'        => 'e-mail',
        ]);

        // Garante que o assessment realmente pertence à empresa logada
        $assessment = SkillAssessment::query()
            ->where('id', $this->inviteAssessmentId)
            ->where('owner_type', 'company')
            ->where('company_profile_id', $cp->id)
            ->first();

        if (! $assessment) {
            $this->addError('inviteAssessmentId', 'Teste inválido para sua empresa.');

            return;
        }

        AssessmentInvitation::create([
            'skill_assessment_id' => $assessment->id,
            'company_profile_id'  => $cp->id,
            'candidate_user_id'   => $this->inviteUserId,
            'candidate_email'     => $this->inviteEmail,
            'expires_at'          => now()->addDays(14),
        ]);

        session()->flash('status', 'Convite enviado com sucesso!');
        $this->closeInviteModal();
    }

    /* ========================================================
     |  Render
     |======================================================== */

    public function render(): View
    {
        // Query base: candidatos com perfil.
        // A contagem de "testes aprovados" é feita via subquery para
        // podermos usá-la também na ordenação.
        $query = User::query()
            ->where('users.type', 'candidate')
            ->has('candidateProfile')
            ->with([
                'candidateProfile:id,user_id,bio,resume_path',
                'candidateProfile.skills:id,name,slug',
            ])
            ->addSelect('users.*')
            ->selectSub(
                \DB::table('skill_assessment_attempts')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('skill_assessment_attempts.user_id', 'users.id')
                    ->where('skill_assessment_attempts.passed', true),
                'passed_assessments_count'
            );

        /* ---------------- Filtros ---------------- */

        $term = trim($this->search);
        if ($term !== '') {
            $like = '%' . $term . '%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('username', 'like', $like)
                    ->orWhere('headline', 'like', $like);
            });
        }

        if (! empty($this->skillNames)) {
            foreach ($this->skillNames as $skillName) {
                $skillName = trim((string) $skillName);
                if ($skillName === '') {
                    continue;
                }
                $query->whereHas('candidateProfile.skills', function (Builder $q) use ($skillName) {
                    $q->whereRaw('LOWER(skills.name) = ?', [mb_strtolower($skillName)]);
                });
            }
        }

        $city = trim($this->city);
        if ($city !== '') {
            $query->where('location', 'like', '%' . $city . '%');
        }

        $uf = trim($this->uf);
        if ($uf !== '') {
            $uf = strtoupper(substr($uf, 0, 2));
            $query->where('location', 'like', '%' . $uf . '%');
        }

        if ($this->openToWork) {
            $query->where('open_to_work', true);
        }

        if ($this->hasResume) {
            $query->whereHas('candidateProfile', function (Builder $q) {
                $q->whereNotNull('resume_path')
                    ->whereNotNull('bio')
                    ->where('bio', '!=', '');
            });
        }

        if ($this->hasAssessments) {
            $query->whereExists(function ($q) {
                $q->select(\DB::raw(1))
                    ->from('skill_assessment_attempts')
                    ->whereColumn('skill_assessment_attempts.user_id', 'users.id')
                    ->where('skill_assessment_attempts.passed', true);
            });
        }

        if ($this->minAssessmentsPassed > 0) {
            $min = (int) $this->minAssessmentsPassed;
            $query->whereRaw(
                '(SELECT COUNT(*) FROM skill_assessment_attempts WHERE skill_assessment_attempts.user_id = users.id AND skill_assessment_attempts.passed = 1) >= ?',
                [$min]
            );
        }

        if ($this->experienceYears !== 'any') {
            // Soma de meses das experiences do candidato
            [$minMonths, $maxMonths] = match ($this->experienceYears) {
                'junior' => [0, 24],           // < 2 anos
                'pleno'  => [24, 60],          // 2–5 anos
                'senior' => [60, PHP_INT_MAX], // 5+ anos
                default  => [0, PHP_INT_MAX],
            };

            if (\Illuminate\Support\Facades\DB::getDriverName() === 'mysql') {
                $sumSql = "(
                    SELECT COALESCE(SUM(
                        TIMESTAMPDIFF(MONTH, experiences.start_date, COALESCE(experiences.end_date, CURRENT_DATE))
                    ), 0)
                    FROM experiences
                    INNER JOIN candidate_profiles ON candidate_profiles.id = experiences.candidate_profile_id
                    WHERE candidate_profiles.user_id = users.id
                )";

                if ($minMonths > 0) {
                    $query->whereRaw("$sumSql >= ?", [$minMonths]);
                }
                if ($maxMonths !== PHP_INT_MAX) {
                    $query->whereRaw("$sumSql < ?", [$maxMonths]);
                }
            }
            // TODO: implementar fallback em PHP para SQLite se necessário
        }

        /* ---------------- Ordenação ---------------- */

        switch ($this->sortBy) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'assessments_desc':
                $query->orderByDesc('passed_assessments_count')
                    ->orderByDesc('users.created_at');
                break;
            case 'recent':
            default:
                $query->orderByDesc('users.created_at');
                break;
        }

        $candidates = $query->paginate(20);

        /* ---------------- Suporte à UI ---------------- */

        // Sugestões de skill para o autocomplete
        $skillSuggestions = [];
        $sq = trim($this->skillQuery);
        if ($sq !== '') {
            $skillSuggestions = Skill::query()
                ->where('name', 'like', '%' . $sq . '%')
                ->orderBy('name')
                ->limit(8)
                ->get(['id', 'name', 'slug']);
        }

        // Testes da empresa logada (para modal)
        $companyAssessments = collect();
        $cp = auth()->user()?->companyProfile;
        if ($cp) {
            $companyAssessments = SkillAssessment::query()
                ->where('owner_type', 'company')
                ->where('company_profile_id', $cp->id)
                ->where('is_active', true)
                ->orderBy('title')
                ->get(['id', 'title', 'category', 'difficulty']);
        }

        return view('livewire.company.talents', [
            'candidates'         => $candidates,
            'skillSuggestions'   => $skillSuggestions,
            'companyAssessments' => $companyAssessments,
        ]);
    }
}
