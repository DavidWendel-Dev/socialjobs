<?php

declare(strict_types=1);

namespace App\Livewire\Company;

use App\Models\Application;
use App\Models\AssessmentInvitation;
use App\Models\JobListing;
use App\Models\SkillAssessmentAttempt;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Dashboard principal da EMPRESA — página /company/dashboard.
 *
 * Overview de KPIs, gráficos (Chart.js) e widgets de atividade
 * recente / vagas em risco. Todos os cálculos são feitos em queries
 * agregadas sobre as tabelas já existentes; nenhum campo novo é criado.
 */
#[Layout('layouts.app')]
#[Title('Dashboard · SocialJobs')]
class Dashboard extends Component
{
    /** Range de datas selecionado: 7d | 30d | 90d | all */
    public string $range = '30d';

    /** Ranges permitidos — evita valores arbitrários via URL / wire:model. */
    private const VALID_RANGES = ['7d', '30d', '90d', 'all'];

    public function mount(): void
    {
        $cp = auth()->user()?->companyProfile;
        abort_unless($cp, 403, 'Perfil de empresa não encontrado.');
    }

    public function updatedRange(string $value): void
    {
        if (! in_array($value, self::VALID_RANGES, true)) {
            $this->range = '30d';
        }
    }

    /**
     * @return array{start: ?Carbon, end: Carbon, days: int, label: string}
     */
    private function resolveRange(): array
    {
        $end = now();

        return match ($this->range) {
            '7d'   => ['start' => $end->copy()->subDays(7),  'end' => $end, 'days' => 7,   'label' => 'Últimos 7 dias'],
            '90d'  => ['start' => $end->copy()->subDays(90), 'end' => $end, 'days' => 90,  'label' => 'Últimos 90 dias'],
            'all'  => ['start' => null,                       'end' => $end, 'days' => 365, 'label' => 'Todo o período'],
            default => ['start' => $end->copy()->subDays(30), 'end' => $end, 'days' => 30,  'label' => 'Últimos 30 dias'],
        };
    }

    public function render(): View
    {
        $cp = auth()->user()->companyProfile;

        $range          = $this->resolveRange();
        $rangeStart     = $range['start'];
        $rangeEnd       = $range['end'];
        $rangeDays      = $range['days'];

        /* --------------------------------------------------------
         * KPIs — Vagas
         * -------------------------------------------------------- */
        $jobs = JobListing::query()->where('company_profile_id', $cp->id);

        $totalJobs  = (clone $jobs)->count();
        $openJobs   = (clone $jobs)->where('status', 'open')->count();
        $closedJobs = max(0, $totalJobs - $openJobs);

        /* --------------------------------------------------------
         * KPIs — Candidaturas no período + variação vs período anterior
         * -------------------------------------------------------- */
        $appsBase = Application::query()
            ->whereHas('jobListing', fn ($q) => $q->where('company_profile_id', $cp->id));

        $appsInRange = (clone $appsBase)
            ->when($rangeStart, fn ($q) => $q->where('created_at', '>=', $rangeStart))
            ->count();

        // Período anterior (mesmo tamanho de janela) para calcular a variação
        if ($rangeStart) {
            $prevStart = $rangeStart->copy()->subDays($rangeDays);
            $prevEnd   = $rangeStart->copy();

            $appsPrevious = (clone $appsBase)
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->count();
        } else {
            $appsPrevious = 0;
        }

        $appsVariation = $appsPrevious > 0
            ? (($appsInRange - $appsPrevious) / $appsPrevious) * 100
            : ($appsInRange > 0 ? 100.0 : 0.0);

        /* --------------------------------------------------------
         * KPI — Taxa de resposta
         * = candidaturas do período que já saíram de "received" / total do período
         * -------------------------------------------------------- */
        $respondedInRange = (clone $appsBase)
            ->when($rangeStart, fn ($q) => $q->where('created_at', '>=', $rangeStart))
            ->where('status', '!=', 'received')
            ->count();

        $responseRate = $appsInRange > 0
            ? ($respondedInRange / $appsInRange) * 100
            : 0.0;

        /* --------------------------------------------------------
         * KPI — Contratações
         * -------------------------------------------------------- */
        $hiredCount = (clone $appsBase)
            ->when($rangeStart, fn ($q) => $q->where('updated_at', '>=', $rangeStart))
            ->where('status', 'hired')
            ->count();

        /* --------------------------------------------------------
         * KPI — Tempo médio de resposta (dias entre created_at e updated_at
         * para applications que já saíram de "received")
         * -------------------------------------------------------- */
        $driver = DB::getDriverName();

        $avgRespondedApps = (clone $appsBase)
            ->when($rangeStart, fn ($q) => $q->where('created_at', '>=', $rangeStart))
            ->where('status', '!=', 'received')
            ->whereColumn('updated_at', '>', 'created_at');

        if ($driver === 'mysql') {
            $avgHours = (clone $avgRespondedApps)
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
                ->value('avg_hours');
            $avgResponseDays = $avgHours !== null
                ? round(((float) $avgHours) / 24, 1)
                : null;
        } else {
            // Fallback (SQLite/Postgres): calcula em PHP
            $sum = 0.0;
            $count = 0;
            (clone $avgRespondedApps)
                ->select(['created_at', 'updated_at'])
                ->chunk(500, function ($rows) use (&$sum, &$count) {
                    foreach ($rows as $r) {
                        if ($r->created_at && $r->updated_at) {
                            $sum += $r->created_at->diffInHours($r->updated_at);
                            $count++;
                        }
                    }
                });
            $avgResponseDays = $count > 0 ? round(($sum / $count) / 24, 1) : null;
        }

        /* --------------------------------------------------------
         * KPIs — Testes (AssessmentInvitations)
         * -------------------------------------------------------- */
        $invBase = AssessmentInvitation::query()->where('company_profile_id', $cp->id);

        $invitesSent = (clone $invBase)
            ->when($rangeStart, fn ($q) => $q->where('created_at', '>=', $rangeStart))
            ->count();

        $invitesCompleted = (clone $invBase)
            ->when($rangeStart, fn ($q) => $q->where('created_at', '>=', $rangeStart))
            ->where('status', 'completed')
            ->count();

        // Taxa de aprovação: tentativas ligadas aos convites da empresa (attempt_id preenchido)
        $attemptIds = (clone $invBase)
            ->whereNotNull('attempt_id')
            ->when($rangeStart, fn ($q) => $q->where('created_at', '>=', $rangeStart))
            ->pluck('attempt_id');

        $totalAttempts = $attemptIds->count();
        $passedAttempts = $totalAttempts > 0
            ? SkillAssessmentAttempt::whereIn('id', $attemptIds)->where('passed', true)->count()
            : 0;

        $passRate = $totalAttempts > 0
            ? ($passedAttempts / $totalAttempts) * 100
            : 0.0;

        /* --------------------------------------------------------
         * Gráfico 1 — Candidaturas por dia (últimos 30 dias, sempre)
         * -------------------------------------------------------- */
        $chartStart = now()->copy()->subDays(29)->startOfDay();

        $daily = (clone $appsBase)
            ->where('created_at', '>=', $chartStart)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as total')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('total', 'd')
            ->all();

        $lineLabels = [];
        $lineData   = [];
        for ($i = 0; $i < 30; $i++) {
            $day = $chartStart->copy()->addDays($i)->format('Y-m-d');
            $lineLabels[] = Carbon::parse($day)->format('d/m');
            $lineData[]   = (int) ($daily[$day] ?? 0);
        }

        /* --------------------------------------------------------
         * Gráfico 2 — Distribuição por status (pizza)
         * -------------------------------------------------------- */
        $statusLabels = [
            'received'  => 'Recebido',
            'reviewing' => 'Em análise',
            'interview' => 'Entrevista',
            'offer'     => 'Oferta',
            'hired'     => 'Contratado',
            'rejected'  => 'Rejeitado',
        ];

        $statusCountsRaw = (clone $appsBase)
            ->when($rangeStart, fn ($q) => $q->where('created_at', '>=', $rangeStart))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $pieLabels = [];
        $pieData   = [];
        foreach ($statusLabels as $key => $label) {
            $pieLabels[] = $label;
            $pieData[]   = (int) ($statusCountsRaw[$key] ?? 0);
        }

        /* --------------------------------------------------------
         * Gráfico 3 — Top 5 vagas com mais candidaturas
         * -------------------------------------------------------- */
        $topJobs = JobListing::query()
            ->where('company_profile_id', $cp->id)
            ->withCount(['applications' => function ($q) use ($rangeStart) {
                if ($rangeStart) {
                    $q->where('created_at', '>=', $rangeStart);
                }
            }])
            ->orderByDesc('applications_count')
            ->limit(5)
            ->get();

        $barLabels = $topJobs->map(fn ($j) => \Illuminate\Support\Str::limit((string) $j->title, 24))->values()->all();
        $barData   = $topJobs->pluck('applications_count')->map(fn ($v) => (int) $v)->values()->all();

        /* --------------------------------------------------------
         * Funil de conversão
         * aplicaram → em análise (reviewing+interview+offer+hired) → entrevista (interview+offer+hired) → contratados
         * -------------------------------------------------------- */
        $funnelApplied = $appsInRange;

        $funnelReview = (clone $appsBase)
            ->when($rangeStart, fn ($q) => $q->where('created_at', '>=', $rangeStart))
            ->whereIn('status', ['reviewing', 'interview', 'offer', 'hired'])
            ->count();

        $funnelInterview = (clone $appsBase)
            ->when($rangeStart, fn ($q) => $q->where('created_at', '>=', $rangeStart))
            ->whereIn('status', ['interview', 'offer', 'hired'])
            ->count();

        $funnelHired = (clone $appsBase)
            ->when($rangeStart, fn ($q) => $q->where('created_at', '>=', $rangeStart))
            ->where('status', 'hired')
            ->count();

        $funnel = [
            ['label' => 'Aplicaram',   'value' => $funnelApplied,   'color' => 'bg-brand-500'],
            ['label' => 'Em análise',  'value' => $funnelReview,    'color' => 'bg-indigo-500'],
            ['label' => 'Entrevista',  'value' => $funnelInterview, 'color' => 'bg-amber-500'],
            ['label' => 'Contratados', 'value' => $funnelHired,     'color' => 'bg-emerald-500'],
        ];

        /* --------------------------------------------------------
         * Widget — Últimas atividades (últimos 30 dias)
         * União de: candidaturas recentes + convites concluídos + mudanças de status
         * -------------------------------------------------------- */
        $activityStart = now()->copy()->subDays(30);

        $recentApps = (clone $appsBase)
            ->with(['user:id,name,username', 'jobListing:id,title,slug'])
            ->where('created_at', '>=', $activityStart)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn (Application $a) => [
                'type'    => 'application',
                'icon'    => 'briefcase',
                'color'   => 'text-brand-600',
                'when'    => $a->created_at,
                'title'   => ($a->user?->name ?? 'Alguém') . ' se candidatou',
                'subtitle' => 'para ' . ($a->jobListing?->title ?? 'vaga removida'),
            ]);

        $recentCompleted = AssessmentInvitation::query()
            ->where('company_profile_id', $cp->id)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $activityStart)
            ->with(['candidate:id,name,username', 'assessment:id,title', 'attempt:id,score,passed'])
            ->orderByDesc('completed_at')
            ->limit(20)
            ->get()
            ->map(function (AssessmentInvitation $inv) {
                $score = $inv->attempt?->score;
                return [
                    'type'    => 'assessment',
                    'icon'    => 'academic',
                    'color'   => ($inv->attempt?->passed ? 'text-emerald-600' : 'text-amber-600'),
                    'when'    => $inv->completed_at,
                    'title'   => ($inv->candidate?->name ?? $inv->candidate_email ?? 'Candidato') . ' concluiu teste',
                    'subtitle' => ($inv->assessment?->title ?? 'Teste') . ($score !== null ? ' • ' . $score . '%' : ''),
                ];
            });

        // Mudanças de status (aproximação: updated_at > created_at e status != received)
        $recentStatus = (clone $appsBase)
            ->with(['user:id,name,username', 'jobListing:id,title'])
            ->where('status', '!=', 'received')
            ->where('updated_at', '>=', $activityStart)
            ->whereColumn('updated_at', '>', 'created_at')
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get()
            ->map(function (Application $a) use ($statusLabels) {
                return [
                    'type'    => 'status',
                    'icon'    => 'check',
                    'color'   => 'text-indigo-600',
                    'when'    => $a->updated_at,
                    'title'   => ($a->user?->name ?? 'Candidato') . ' movido para ' . ($statusLabels[$a->status] ?? $a->status),
                    'subtitle' => 'em ' . ($a->jobListing?->title ?? 'vaga'),
                ];
            });

        $activities = $recentApps
            ->concat($recentCompleted)
            ->concat($recentStatus)
            ->sortByDesc('when')
            ->take(20)
            ->values();

        /* --------------------------------------------------------
         * Widget — Vagas em risco
         * vagas abertas há mais de 30 dias sem candidatos OU
         * cuja última movimentação de application foi há mais de 14 dias
         * -------------------------------------------------------- */
        $atRiskJobs = JobListing::query()
            ->where('company_profile_id', $cp->id)
            ->where('status', 'open')
            ->where('created_at', '<=', now()->subDays(30))
            ->withCount('applications')
            ->with(['applications' => fn ($q) => $q->latest('updated_at')->limit(1)])
            ->get()
            ->filter(function (JobListing $j) {
                if ($j->applications_count === 0) {
                    return true;
                }
                $last = $j->applications->first();
                return $last && $last->updated_at && $last->updated_at->lt(now()->subDays(14));
            })
            ->take(6)
            ->values();

        return view('livewire.company.dashboard', [
            'rangeLabel'       => $range['label'],
            // KPIs
            'totalJobs'        => $totalJobs,
            'openJobs'         => $openJobs,
            'closedJobs'       => $closedJobs,
            'appsInRange'      => $appsInRange,
            'appsPrevious'     => $appsPrevious,
            'appsVariation'    => $appsVariation,
            'responseRate'     => $responseRate,
            'hiredCount'       => $hiredCount,
            'avgResponseDays'  => $avgResponseDays,
            'invitesSent'      => $invitesSent,
            'invitesCompleted' => $invitesCompleted,
            'passRate'         => $passRate,
            'totalAttempts'    => $totalAttempts,
            'passedAttempts'   => $passedAttempts,
            // Charts
            'lineLabels'       => $lineLabels,
            'lineData'         => $lineData,
            'pieLabels'        => $pieLabels,
            'pieData'          => $pieData,
            'barLabels'        => $barLabels,
            'barData'          => $barData,
            'funnel'           => $funnel,
            // Widgets
            'activities'       => $activities,
            'atRiskJobs'       => $atRiskJobs,
        ]);
    }
}
