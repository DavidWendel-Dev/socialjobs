<?php

declare(strict_types=1);

namespace App\Livewire\Points;

use App\Models\Badge;
use App\Models\PointEvent;
use App\Services\LevelService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pontos e nível · SocialJobs')]
class Dashboard extends Component
{
    public string $tab = 'history';

    /**
     * Rótulos amigáveis para cada ação do sistema de pontos.
     * As chaves devem bater com `config/points.php` -> 'actions'.
     */
    public array $actionLabels = [
        'profile.completed'       => 'Perfil completo',
        'email.verified'          => 'E-mail verificado',
        '2fa.enabled'             => 'Ativou autenticação em 2 fatores',
        'post.first'              => 'Primeira publicação',
        'post.created'            => 'Publicou um post',
        'reaction.received'       => 'Recebeu uma reação',
        'comment.created'         => 'Comentou em um post',
        'follower.gained'         => 'Ganhou um seguidor',
        'endorsement.given'       => 'Endossou uma habilidade',
        'endorsement.received'    => 'Recebeu um endosso',
        'recommendation.received' => 'Recebeu uma recomendação',
        'application.sent'        => 'Candidatou-se a uma vaga',
        'application.hired'       => 'Foi contratado! 🎉',
        'lesson.completed'        => 'Concluiu uma aula',
        'module.passed'           => 'Passou em um módulo',
        'course.completed'        => 'Concluiu um curso',
        'interview.simulated'    => 'Simulou uma entrevista',
        'interview.high_score'   => 'Alta pontuação em entrevista',
        'login.daily'             => 'Login diário',
        'login.streak_week'       => 'Streak semanal',
        'report.validated'        => 'Denúncia validada',
    ];

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['history', 'badges', 'ranking'], true) ? $tab : 'history';
    }

    public function render()
    {
        $user      = auth()->user();
        $totalXp   = (int) ($user?->stats?->total_xp ?? 0);
        $level     = app(LevelService::class)->computeLevel($totalXp);

        $events = $user
            ? PointEvent::query()
                ->where('user_id', $user->id)
                ->latest()
                ->take(30)
                ->get()
            : collect();

        $badges       = Badge::query()->limit(20)->get();
        $userBadgeIds = $user ? $user->badges()->pluck('badges.id')->all() : [];

        return view('livewire.points.dashboard', [
            'totalXp'      => $totalXp,
            'level'        => $level,
            'events'       => $events,
            'badges'       => $badges,
            'userBadgeIds' => $userBadgeIds,
        ]);
    }
}
