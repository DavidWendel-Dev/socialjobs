<?php

declare(strict_types=1);

namespace App\Livewire\Search;

use App\Models\Course;
use App\Models\JobListing;
use App\Models\Post;
use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Barra de busca com autocomplete em tempo real.
 * Fica no header. Ao digitar mostra até 3 resultados por categoria.
 * Ao dar Enter (ou clicar em "Ver todos"), navega para /search?q=...
 */
class Bar extends Component
{
    #[Url]
    public string $q = '';

    /** Controla se o dropdown está aberto (Alpine também gerencia visual). */
    public bool $open = false;

    public function updatedQ(): void
    {
        $this->open = trim($this->q) !== '';
    }

    /**
     * Chamado ao dar Enter no input — navega para a página de resultados.
     */
    public function goToResults(): void
    {
        $q = trim($this->q);
        if ($q === '') {
            return;
        }

        $this->redirect(route('search') . '?q=' . urlencode($q), navigate: true);
    }

    public function clear(): void
    {
        $this->q = '';
        $this->open = false;
    }

    public function render()
    {
        $term = trim($this->q);
        $like = '%' . $term . '%';

        $people    = collect();
        $companies = collect();
        $jobs      = collect();
        $posts     = collect();
        $courses   = collect();

        // Só busca quando há pelo menos 2 caracteres (evita queries pesadas em cada tecla)
        if (mb_strlen($term) >= 2) {
            $people = User::query()
                ->where('type', 'candidate')
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                      ->orWhere('username', 'like', $like)
                      ->orWhere('headline', 'like', $like);
                })
                ->limit(3)
                ->get(['id', 'name', 'username', 'headline', 'avatar_path']);

            $companies = User::query()
                ->where('type', 'company')
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                      ->orWhere('username', 'like', $like)
                      // Também busca no CompanyProfile (razão social / nome fantasia / CNPJ / setor)
                      ->orWhereHas('companyProfile', function ($cp) use ($like) {
                          $cp->where('legal_name', 'like', $like)
                             ->orWhere('trade_name', 'like', $like)
                             ->orWhere('slug', 'like', $like)
                             ->orWhere('cnpj', 'like', $like)
                             ->orWhere('industry', 'like', $like);
                      });
                })
                ->with('companyProfile:id,user_id,legal_name,trade_name,slug,logo_path')
                ->limit(3)
                ->get();

            $jobs = JobListing::query()
                ->where('status', 'open')
                ->where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)
                      ->orWhere('description', 'like', $like)
                      ->orWhere('location', 'like', $like)
                      // Busca também pelo nome da empresa
                      ->orWhereHas('companyProfile', function ($cp) use ($like) {
                          $cp->where('legal_name', 'like', $like)
                             ->orWhere('trade_name', 'like', $like);
                      });
                })
                ->with('companyProfile:id,legal_name,trade_name')
                ->limit(3)
                ->get(['id', 'title', 'location', 'company_profile_id']);

            $posts = Post::query()
                ->where('body', 'like', $like)
                ->where('visibility', 'public')
                ->with('user:id,name,username,avatar_path')
                ->latest()
                ->limit(3)
                ->get(['id', 'body', 'user_id', 'created_at']);

            $courses = Course::query()
                ->where('status', 'published')
                ->where('title', 'like', $like)
                ->limit(3)
                ->get(['id', 'title', 'slug', 'level']);
        }

        $hasResults = $people->count() + $companies->count() + $jobs->count() + $posts->count() + $courses->count();

        return view('livewire.search.bar', [
            'people'     => $people,
            'companies'  => $companies,
            'jobs'       => $jobs,
            'posts'      => $posts,
            'courses'    => $courses,
            'hasResults' => $hasResults,
            'term'       => $term,
        ]);
    }
}
