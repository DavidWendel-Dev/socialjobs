<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Enrollment;
use App\Models\InterviewSession;
use App\Models\Post;
use App\Models\User;
use App\Services\SkillAssessmentService;
use Illuminate\Support\Collection;

/**
 * Agrega todos os dados do Currículo Digital de um usuário.
 *
 * Este service é a fonte única de verdade da página pública `/cv/{username}`
 * e da aba "Currículo" no perfil autenticado. Retorna uma estrutura pronta
 * para a view, sem lógica de banco na Blade.
 */
class CurriculumService
{
    /**
     * Monta o "pacote" completo do CV.
     *
     * @return array{
     *   user: User,
     *   profile: ?\App\Models\CandidateProfile,
     *   sections: array<string, bool>,
     *   about: array,
     *   xp: array,
     *   skills: \Illuminate\Support\Collection,
     *   experiences: \Illuminate\Support\Collection,
     *   educations: \Illuminate\Support\Collection,
     *   portfolio: \Illuminate\Support\Collection,
     *   courses_completed: \Illuminate\Support\Collection,
     *   interviews: \Illuminate\Support\Collection,
     *   featured_posts: \Illuminate\Support\Collection,
     *   stats: array,
     * }
     */
    public function buildFor(User $user): array
    {
        $user->loadMissing([
            'candidateProfile.skills',
            'candidateProfile.experiences' => fn ($q) => $q->orderByDesc('current')->orderByDesc('start_date'),
            'candidateProfile.educations'  => fn ($q) => $q->orderByDesc('start_date'),
            'candidateProfile.portfolioItems',
            'stats',
        ]);

        $profile = $user->candidateProfile;

        // Cursos concluídos com progresso 100%
        $coursesCompleted = Enrollment::query()
            ->with('course:id,title,slug,level,category,total_minutes,xp_reward')
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->get()
            ->filter(fn ($e) => $e->course !== null)
            ->values();

        // Entrevistas com score (finalizadas)
        $interviews = InterviewSession::query()
            ->where('user_id', $user->id)
            ->where('status', 'finished')
            ->whereNotNull('overall_score')
            ->orderByDesc('finished_at')
            ->limit(6)
            ->get();

        // Testes de proficiência aprovados (badges verificados)
        $skillBadges = app(SkillAssessmentService::class)->bestScoresFor($user);

        // Posts marcados como destaque pelo autor
        $featuredPosts = Post::query()
            ->with('user:id,name,username,avatar_path')
            ->where('user_id', $user->id)
            ->where('is_featured', true)
            ->where('visibility', 'public')
            ->latest()
            ->limit(6)
            ->get();

        // Estatísticas agregadas do site (todos verificados)
        $stats = [
            'total_xp'          => (int) ($user->stats?->total_xp ?? 0),
            'level'             => (int) ($user->stats?->level ?? 1),
            'courses_count'     => $coursesCompleted->count(),
            'total_hours'       => (int) round($coursesCompleted->sum(fn ($e) => (int) ($e->course->total_minutes ?? 0)) / 60),
            'interviews_count'  => $interviews->count(),
            'avg_interview'     => $interviews->count() > 0
                ? round($interviews->avg('overall_score'))
                : null,
            'skills_count'      => $profile?->skills?->count() ?? 0,
            'experiences_count' => $profile?->experiences?->count() ?? 0,
        ];

        return [
            'user'              => $user,
            'profile'           => $profile,
            'about'             => [
                'name'         => (string) $user->name,
                'headline'     => (string) ($user->headline ?? ''),
                'bio'          => (string) ($profile?->bio ?? ''),
                'location'     => (string) ($user->location ?? ''),
                'open_to_work' => (bool) $user->open_to_work,
                'links'        => [
                    'linkedin'  => $profile?->linkedin_url,
                    'github'    => $profile?->github_url,
                    'portfolio' => $profile?->portfolio_url,
                ],
            ],
            'skills'            => $profile?->skills ?? collect(),
            'experiences'       => $profile?->experiences ?? collect(),
            'educations'        => $profile?->educations ?? collect(),
            'portfolio'         => $profile?->portfolioItems ?? collect(),
            'courses_completed' => $coursesCompleted,
            'interviews'        => $interviews,
            'skill_badges'      => $skillBadges,
            'featured_posts'    => $featuredPosts,
            'stats'             => $stats,
        ];
    }
}
