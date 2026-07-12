<?php

declare(strict_types=1);

namespace App\Livewire\Sidebar;

use App\Models\Application;
use App\Models\JobListing;
use App\Services\JobMatchingService;
use Livewire\Component;

/**
 * Sidebar contextual para as páginas de vagas.
 * Mostra vagas com melhor score de compatibilidade com o perfil do usuário logado.
 */
class RecommendedJobs extends Component
{
    /** Quantas vagas mostrar. */
    public int $limit = 5;

    public function render()
    {
        $user = auth()->user();

        if (! $user) {
            return view('livewire.sidebar.recommended-jobs', ['jobs' => collect()]);
        }

        // IDs de vagas em que o usuário já se candidatou (para excluir)
        $appliedIds = Application::query()
            ->where('user_id', $user->id)
            ->pluck('job_listing_id')
            ->all();

        try {
            $jobs = app(JobMatchingService::class)
                ->recommend($user, $this->limit + count($appliedIds));

            // Remove vagas já aplicadas + limita
            $jobs = $jobs
                ->reject(fn ($j) => in_array($j->id, $appliedIds, true))
                ->take($this->limit)
                ->values();
        } catch (\Throwable $e) {
            // Fallback: só as mais recentes abertas
            $jobs = JobListing::query()
                ->where('status', 'open')
                ->whereNotIn('id', $appliedIds)
                ->with('companyProfile')
                ->latest('published_at')
                ->limit($this->limit)
                ->get();
        }

        return view('livewire.sidebar.recommended-jobs', [
            'jobs' => $jobs,
        ]);
    }
}
