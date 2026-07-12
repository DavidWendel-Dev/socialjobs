<?php

declare(strict_types=1);

namespace App\Livewire\Search;

use App\Models\Course;
use App\Models\JobListing;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Buscar · SocialJobs')]
class Results extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $q = '';

    /**
     * Filtro por tipo:
     *  - all       (padrão) mostra as 5 seções lado a lado
     *  - people    apenas candidatos
     *  - companies apenas empresas
     *  - jobs      apenas vagas
     *  - courses   apenas cursos
     *  - posts     apenas posts
     */
    #[Url(as: 't')]
    public string $type = 'all';

    /** Ordenação: relevance (recentes), name, oldest. */
    #[Url(as: 'sort')]
    public string $sort = 'relevance';

    public function updatingType(): void { $this->resetPage(); }
    public function updatingSort(): void { $this->resetPage(); }
    public function updatingQ():    void { $this->resetPage(); }

    public function setType(string $t): void
    {
        $this->type = in_array($t, ['all', 'people', 'companies', 'jobs', 'courses', 'posts'], true) ? $t : 'all';
    }

    public function render(): View
    {
        $term = trim($this->q);
        $like = '%' . $term . '%';

        // Contadores para os chips de tipo
        $counts = [
            'people'    => 0,
            'companies' => 0,
            'jobs'      => 0,
            'courses'   => 0,
            'posts'     => 0,
        ];

        $results = [
            'people'    => collect(),
            'companies' => collect(),
            'jobs'      => collect(),
            'courses'   => collect(),
            'posts'     => collect(),
        ];

        if (mb_strlen($term) >= 2) {
            // ------------------------ PEOPLE ------------------------
            $peopleQ = User::query()
                ->where('type', 'candidate')
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                      ->orWhere('username', 'like', $like)
                      ->orWhere('headline', 'like', $like);
                });
            $counts['people'] = (clone $peopleQ)->count();

            // ---------------------- COMPANIES -----------------------
            $companiesQ = User::query()
                ->where('type', 'company')
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                      ->orWhere('username', 'like', $like)
                      // Busca também nos campos do CompanyProfile (nome fantasia é o mais comum)
                      ->orWhereHas('companyProfile', function ($cp) use ($like) {
                          $cp->where('legal_name', 'like', $like)
                             ->orWhere('trade_name', 'like', $like)
                             ->orWhere('slug', 'like', $like)
                             ->orWhere('cnpj', 'like', $like)
                             ->orWhere('industry', 'like', $like)
                             ->orWhere('about', 'like', $like);
                      });
                })
                ->with('companyProfile');
            $counts['companies'] = (clone $companiesQ)->count();

            // ------------------------ JOBS --------------------------
            $jobsQ = JobListing::query()
                ->where('status', 'open')
                ->where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)
                      ->orWhere('description', 'like', $like)
                      ->orWhere('location', 'like', $like)
                      // Busca também pelo nome da empresa que publica a vaga
                      ->orWhereHas('companyProfile', function ($cp) use ($like) {
                          $cp->where('legal_name', 'like', $like)
                             ->orWhere('trade_name', 'like', $like);
                      });
                })
                ->with('companyProfile');
            $counts['jobs'] = (clone $jobsQ)->count();

            // ----------------------- COURSES ------------------------
            $coursesQ = Course::query()
                ->where('status', 'published')
                ->where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)
                      ->orWhere('description', 'like', $like);
                });
            $counts['courses'] = (clone $coursesQ)->count();

            // ------------------------ POSTS -------------------------
            $postsQ = Post::query()
                ->where('body', 'like', $like)
                ->where('visibility', 'public')
                ->with('user');
            $counts['posts'] = (clone $postsQ)->count();

            // Ordenação
            $applySort = function ($q, string $nameCol = 'name', string $dateCol = 'created_at') {
                return match ($this->sort) {
                    'name'    => $q->orderBy($nameCol),
                    'oldest'  => $q->orderBy($dateCol),
                    default   => $q->latest($dateCol),
                };
            };

            // Quantidade retornada depende se é "all" (só 6 por seção) ou específico (paginado 20)
            $limitPerSection = $this->type === 'all' ? 6 : null;

            $results['people'] = $limitPerSection
                ? $applySort($peopleQ, 'name')->limit($limitPerSection)->get()
                : ($this->type === 'people' ? $applySort($peopleQ, 'name')->paginate(20, ['*'], 'peoplePage') : collect());

            $results['companies'] = $limitPerSection
                ? $applySort($companiesQ, 'name')->limit($limitPerSection)->get()
                : ($this->type === 'companies' ? $applySort($companiesQ, 'name')->paginate(20, ['*'], 'companiesPage') : collect());

            $results['jobs'] = $limitPerSection
                ? $applySort($jobsQ, 'title', 'published_at')->limit($limitPerSection)->get()
                : ($this->type === 'jobs' ? $applySort($jobsQ, 'title', 'published_at')->paginate(20, ['*'], 'jobsPage') : collect());

            $results['courses'] = $limitPerSection
                ? $applySort($coursesQ, 'title')->limit($limitPerSection)->get()
                : ($this->type === 'courses' ? $applySort($coursesQ, 'title')->paginate(20, ['*'], 'coursesPage') : collect());

            $results['posts'] = $limitPerSection
                ? $applySort($postsQ)->limit($limitPerSection)->get()
                : ($this->type === 'posts' ? $applySort($postsQ)->paginate(20, ['*'], 'postsPage') : collect());
        }

        $totalCount = array_sum($counts);

        return view('livewire.search.results', [
            'term'       => $term,
            'counts'     => $counts,
            'results'    => $results,
            'totalCount' => $totalCount,
        ]);
    }
}
