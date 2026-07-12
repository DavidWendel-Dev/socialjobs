<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Cálculo de match candidato x vaga.
 * Estratégia híbrida: skills em comum + preferências + boost opcional da IA.
 */
class JobMatchingService
{
    public function __construct(private ?AiService $ai = null)
    {
    }

    /**
     * Retorna score 0-100.
     */
    public function scoreFor(User $user, JobListing $job): float
    {
        $profile = $user->candidateProfile;
        if (! $profile) {
            return 0.0;
        }

        $userSkills = $profile->skills()->pluck('skills.id')->all();
        $jobSkills  = $job->skills()->pluck('skills.id')->all();

        if (empty($jobSkills)) {
            // Sem skills mapeadas, usamos apenas heurística de modalidade/localização
            $base = 50.0;
        } else {
            $matches = count(array_intersect($userSkills, $jobSkills));
            $base    = ($matches / max(1, count($jobSkills))) * 80.0;
        }

        // Boost por preferência de modalidade
        $prefs = (array) ($profile->preferences ?? []);
        if (($prefs['modality'] ?? null) === $job->modality) {
            $base += 10;
        }
        if (($prefs['seniority'] ?? null) === $job->seniority) {
            $base += 10;
        }

        return (float) min(100.0, round($base, 2));
    }

    /**
     * Retorna vagas recomendadas ordenadas por score.
     *
     * @return Collection<int, JobListing>
     */
    public function recommend(User $user, int $limit = 10): Collection
    {
        $jobs = JobListing::query()
            ->where('status', 'open')
            ->latest('published_at')
            ->limit(60)
            ->get();

        return $jobs
            ->map(function (JobListing $job) use ($user) {
                $job->setAttribute('match_score', $this->scoreFor($user, $job));

                return $job;
            })
            ->sortByDesc('match_score')
            ->take($limit)
            ->values();
    }
}
