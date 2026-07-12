<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\CandidateProfile;
use App\Models\Education;
use App\Models\Experience;
use App\Models\PortfolioItem;
use App\Models\Skill;
use App\Services\PointsService;
use App\Support\Media;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Editar perfil · SocialJobs')]
class Edit extends Component
{
    use WithFileUploads;

    // Dados do próprio user (tabela users)
    #[Validate('required|string|min:2|max:120')]
    public string $name = '';

    #[Validate('required|alpha_dash|min:3|max:40')]
    public string $username = '';

    #[Validate('nullable|string|max:180')]
    public string $headline = '';

    #[Validate('nullable|string|max:120')]
    public string $location = '';

    public bool $open_to_work = false;

    // Dados do candidate_profile
    #[Validate('nullable|string|max:2000')]
    public string $bio = '';

    #[Validate('nullable|url|max:191')]
    public string $linkedin_url = '';

    #[Validate('nullable|url|max:191')]
    public string $github_url = '';

    #[Validate('nullable|url|max:191')]
    public string $portfolio_url = '';

    // Uploads
    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:2048')]
    public $avatarUpload = null;

    #[Validate('nullable|image|mimes:jpg,jpeg,png,webp|max:5120')]
    public $coverUpload = null;

    // Skills — array de nomes (strings)
    /** @var array<int,string> */
    public array $skills = [];
    public string $skillInput = '';

    public function mount(): void
    {
        $u = auth()->user();

        $this->name          = $u->name ?? '';
        $this->username      = $u->username ?? '';
        $this->headline      = $u->headline ?? '';
        $this->location      = $u->location ?? '';
        $this->open_to_work  = (bool) $u->open_to_work;

        $profile = $u->candidateProfile;
        if ($profile) {
            $this->bio           = $profile->bio ?? '';
            $this->linkedin_url  = $profile->linkedin_url ?? '';
            $this->github_url    = $profile->github_url ?? '';
            $this->portfolio_url = $profile->portfolio_url ?? '';
            $this->skills        = $profile->skills()->pluck('name')->all();
        }
    }

    /* ============================================================
     |  Skills — adicionar/remover na lista antes de salvar
     |============================================================ */

    public function addSkill(): void
    {
        $skill = trim($this->skillInput);
        if ($skill === '') {
            return;
        }
        if (in_array($skill, $this->skills, true)) {
            $this->skillInput = '';
            return;
        }
        if (count($this->skills) >= 20) {
            $this->addError('skillInput', 'Você pode ter no máximo 20 skills.');
            return;
        }

        $this->skills[] = $skill;
        $this->skillInput = '';
    }

    public function removeSkill(int $index): void
    {
        unset($this->skills[$index]);
        $this->skills = array_values($this->skills);
    }

    /* ============================================================
     |  Experiências profissionais — CRUD inline
     |============================================================ */

    /** Formulário aberto para editar uma experiência (id null = criar nova). */
    public ?int $editingExperienceId = null;

    /** Campos do formulário de experiência em edição. */
    public string $expCompany    = '';
    public string $expRole       = '';
    public string $expStartDate  = '';
    public string $expEndDate    = '';
    public bool   $expCurrent    = false;
    public string $expDescription = '';

    /**
     * Abre o painel de nova experiência (limpo).
     */
    public function newExperience(): void
    {
        $this->resetExperienceForm();
        $this->editingExperienceId = 0; // 0 = modo "criar"
    }

    /**
     * Carrega uma experiência existente no formulário para edição.
     */
    public function editExperience(int $id): void
    {
        $exp = $this->getProfile()?->experiences()->find($id);
        if (! $exp) return;

        $this->editingExperienceId = $exp->id;
        $this->expCompany     = (string) $exp->company_name;
        $this->expRole        = (string) $exp->role;
        $this->expStartDate   = optional($exp->start_date)->format('Y-m-d') ?? '';
        $this->expEndDate     = optional($exp->end_date)->format('Y-m-d') ?? '';
        $this->expCurrent     = (bool) $exp->current;
        $this->expDescription = (string) $exp->description;
    }

    /**
     * Salva (cria ou atualiza) a experiência em edição.
     */
    public function saveExperience(): void
    {
        $this->validate([
            'expCompany'     => 'required|string|min:2|max:255',
            'expRole'        => 'required|string|min:2|max:255',
            'expStartDate'   => 'required|date',
            'expEndDate'     => 'nullable|date|after_or_equal:expStartDate',
            'expDescription' => 'nullable|string|max:2000',
        ]);

        $profile = $this->getProfile();
        if (! $profile) return;

        $data = [
            'company_name' => $this->expCompany,
            'role'         => $this->expRole,
            'start_date'   => $this->expStartDate,
            'end_date'     => $this->expCurrent ? null : ($this->expEndDate ?: null),
            'current'      => $this->expCurrent,
            'description'  => $this->expDescription ?: null,
        ];

        if ($this->editingExperienceId && $this->editingExperienceId > 0) {
            $profile->experiences()->where('id', $this->editingExperienceId)->update($data);
        } else {
            $profile->experiences()->create($data);
        }

        $this->cancelExperience();
        session()->flash('status', 'Experiência salva.');
    }

    public function cancelExperience(): void
    {
        $this->editingExperienceId = null;
        $this->resetExperienceForm();
    }

    public function deleteExperience(int $id): void
    {
        $this->getProfile()?->experiences()->where('id', $id)->delete();
        session()->flash('status', 'Experiência removida.');
    }

    private function resetExperienceForm(): void
    {
        $this->expCompany     = '';
        $this->expRole        = '';
        $this->expStartDate   = '';
        $this->expEndDate     = '';
        $this->expCurrent     = false;
        $this->expDescription = '';
    }

    /* ============================================================
     |  Formação acadêmica — CRUD inline
     |============================================================ */

    public ?int $editingEducationId = null;
    public string $eduInstitution = '';
    public string $eduDegree      = '';
    public string $eduStartDate   = '';
    public string $eduEndDate     = '';

    public function newEducation(): void
    {
        $this->resetEducationForm();
        $this->editingEducationId = 0;
    }

    public function editEducation(int $id): void
    {
        $ed = $this->getProfile()?->educations()->find($id);
        if (! $ed) return;

        $this->editingEducationId = $ed->id;
        $this->eduInstitution = (string) $ed->institution;
        $this->eduDegree      = (string) $ed->degree;
        $this->eduStartDate   = optional($ed->start_date)->format('Y-m-d') ?? '';
        $this->eduEndDate     = optional($ed->end_date)->format('Y-m-d') ?? '';
    }

    public function saveEducation(): void
    {
        $this->validate([
            'eduInstitution' => 'required|string|min:2|max:255',
            'eduDegree'      => 'required|string|min:2|max:255',
            'eduStartDate'   => 'required|date',
            'eduEndDate'     => 'nullable|date|after_or_equal:eduStartDate',
        ]);

        $profile = $this->getProfile();
        if (! $profile) return;

        $data = [
            'institution' => $this->eduInstitution,
            'degree'      => $this->eduDegree,
            'start_date'  => $this->eduStartDate,
            'end_date'    => $this->eduEndDate ?: null,
        ];

        if ($this->editingEducationId && $this->editingEducationId > 0) {
            $profile->educations()->where('id', $this->editingEducationId)->update($data);
        } else {
            $profile->educations()->create($data);
        }

        $this->cancelEducation();
        session()->flash('status', 'Formação salva.');
    }

    public function cancelEducation(): void
    {
        $this->editingEducationId = null;
        $this->resetEducationForm();
    }

    public function deleteEducation(int $id): void
    {
        $this->getProfile()?->educations()->where('id', $id)->delete();
        session()->flash('status', 'Formação removida.');
    }

    private function resetEducationForm(): void
    {
        $this->eduInstitution = '';
        $this->eduDegree      = '';
        $this->eduStartDate   = '';
        $this->eduEndDate     = '';
    }

    /* ============================================================
     |  Portfólio — CRUD inline
     |============================================================ */

    public ?int $editingPortfolioId = null;
    public string $portTitle       = '';
    public string $portUrl         = '';
    public string $portDescription = '';

    public function newPortfolio(): void
    {
        $this->resetPortfolioForm();
        $this->editingPortfolioId = 0;
    }

    public function editPortfolio(int $id): void
    {
        $item = $this->getProfile()?->portfolioItems()->find($id);
        if (! $item) return;

        $this->editingPortfolioId = $item->id;
        $this->portTitle       = (string) $item->title;
        $this->portUrl         = (string) $item->url;
        $this->portDescription = (string) $item->description;
    }

    public function savePortfolio(): void
    {
        $this->validate([
            'portTitle'       => 'required|string|min:2|max:255',
            'portUrl'         => 'nullable|url|max:255',
            'portDescription' => 'nullable|string|max:1000',
        ]);

        $profile = $this->getProfile();
        if (! $profile) return;

        $data = [
            'title'       => $this->portTitle,
            'url'         => $this->portUrl ?: null,
            'description' => $this->portDescription ?: null,
        ];

        if ($this->editingPortfolioId && $this->editingPortfolioId > 0) {
            $profile->portfolioItems()->where('id', $this->editingPortfolioId)->update($data);
        } else {
            $profile->portfolioItems()->create($data);
        }

        $this->cancelPortfolio();
        session()->flash('status', 'Item do portfólio salvo.');
    }

    public function cancelPortfolio(): void
    {
        $this->editingPortfolioId = null;
        $this->resetPortfolioForm();
    }

    public function deletePortfolio(int $id): void
    {
        $this->getProfile()?->portfolioItems()->where('id', $id)->delete();
        session()->flash('status', 'Item removido.');
    }

    private function resetPortfolioForm(): void
    {
        $this->portTitle       = '';
        $this->portUrl         = '';
        $this->portDescription = '';
    }

    /**
     * Helper: obtém (ou cria) o CandidateProfile do usuário logado.
     */
    private function getProfile(): ?CandidateProfile
    {
        $u = auth()->user();
        if (! $u) return null;
        return $u->candidateProfile ?? CandidateProfile::firstOrCreate(['user_id' => $u->id]);
    }

    /* ============================================================
     |  Uploads instantâneos
     |============================================================ */

    public function updatedAvatarUpload(): void
    {
        $this->validateOnly('avatarUpload');

        // Bloqueia foto de perfil imprópria (Oanor NSFW).
        // Se a Oanor estiver fora, fail-open (foto passa e é logada).
        if (! app(\App\Services\NsfwScanner::class)->isSafe($this->avatarUpload)) {
            $this->avatarUpload = null;
            $this->addError('avatarUpload', 'Esta imagem foi bloqueada por conter conteúdo impróprio. Envie outra foto.');
            return;
        }

        $u = auth()->user();

        if ($u->avatar_path && Media::exists($u->avatar_path)) {
            Media::delete($u->avatar_path);
        }

        $u->avatar_path = Media::store($this->avatarUpload, 'avatars');
        $u->save();

        $this->avatarUpload = null;
        session()->flash('status', 'Foto de perfil atualizada!');
    }

    public function updatedCoverUpload(): void
    {
        $this->validateOnly('coverUpload');

        // Bloqueia capa imprópria (Oanor NSFW).
        if (! app(\App\Services\NsfwScanner::class)->isSafe($this->coverUpload)) {
            $this->coverUpload = null;
            $this->addError('coverUpload', 'Esta imagem foi bloqueada por conter conteúdo impróprio. Envie outra capa.');
            return;
        }

        $u = auth()->user();

        if ($u->cover_path && Media::exists($u->cover_path)) {
            Media::delete($u->cover_path);
        }

        $u->cover_path = Media::store($this->coverUpload, 'covers');
        $u->save();

        $this->coverUpload = null;
        session()->flash('status', 'Capa atualizada!');
    }

    public function removeAvatar(): void
    {
        $u = auth()->user();
        if ($u->avatar_path && Media::exists($u->avatar_path)) {
            Media::delete($u->avatar_path);
        }
        $u->avatar_path = null;
        $u->save();
        session()->flash('status', 'Foto de perfil removida.');
    }

    public function removeCover(): void
    {
        $u = auth()->user();
        if ($u->cover_path && Media::exists($u->cover_path)) {
            Media::delete($u->cover_path);
        }
        $u->cover_path = null;
        $u->save();
        session()->flash('status', 'Capa removida.');
    }

    /* ============================================================
     |  Salvar todo o formulário
     |============================================================ */

    public function save(): void
    {
        $this->validate();

        $u = auth()->user();

        // Verifica se o username está sendo alterado e já é usado
        if ($this->username !== $u->username) {
            $exists = \App\Models\User::where('username', $this->username)
                ->where('id', '!=', $u->id)
                ->exists();
            if ($exists) {
                $this->addError('username', 'Este username já está em uso.');
                return;
            }
        }

        DB::transaction(function () use ($u) {
            // 1) Users
            $u->fill([
                'name'         => $this->name,
                'username'     => $this->username,
                'headline'     => $this->headline ?: null,
                'location'     => $this->location ?: null,
                'open_to_work' => $this->open_to_work,
            ])->save();

            // 2) CandidateProfile
            $profile = CandidateProfile::firstOrCreate(['user_id' => $u->id]);
            $profile->fill([
                'bio'           => $this->bio ?: null,
                'linkedin_url'  => $this->linkedin_url ?: null,
                'github_url'    => $this->github_url ?: null,
                'portfolio_url' => $this->portfolio_url ?: null,
            ])->save();

            // 3) Skills — sincroniza a lista
            $skillIds = [];
            foreach ($this->skills as $name) {
                $name = trim($name);
                if ($name === '') {
                    continue;
                }
                $skill = Skill::firstOrCreate(
                    ['name' => $name],
                    ['slug' => Str::slug($name)]
                );
                $skillIds[] = $skill->id;
            }
            $profile->skills()->sync($skillIds);
        });

        // Concede XP pela primeira vez que completa o perfil
        try {
            app(PointsService::class)->award(auth()->user(), 'profile.completed');
        } catch (\Throwable $e) {
            report($e);
        }

        session()->flash('status', 'Perfil atualizado! 🎉');
    }

    public function render(): View
    {
        // Carrega listas para os cards de experiência/formação/portfólio
        $profile = auth()->user()?->candidateProfile;

        return view('livewire.profile.edit', [
            'experiences' => $profile
                ? $profile->experiences()->orderByDesc('current')->orderByDesc('start_date')->get()
                : collect(),
            'educations'  => $profile
                ? $profile->educations()->orderByDesc('start_date')->get()
                : collect(),
            'portfolioItems' => $profile
                ? $profile->portfolioItems()->orderBy('id')->get()
                : collect(),
        ]);
    }
}
