<div class="space-y-6" wire:key="company-dashboard">
    {{-- ===============================================================
         Cabeçalho + seletor de período
         =============================================================== --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-xl sm:text-2xl font-bold tracking-tight">
                Dashboard
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Visão geral da sua operação de recrutamento — {{ $rangeLabel }}.
            </p>
        </div>

        <div class="flex items-center gap-1 rounded-full bg-slate-100 p-1 dark:bg-slate-800">
            @foreach ([
                '7d'  => '7d',
                '30d' => '30d',
                '90d' => '90d',
                'all' => 'Tudo',
            ] as $value => $label)
                <button type="button"
                        wire:click="$set('range', '{{ $value }}')"
                        class="rounded-full px-3 py-1.5 text-xs sm:text-sm font-semibold transition
                               {{ $range === $value
                                   ? 'bg-white text-brand-700 shadow-soft dark:bg-slate-900 dark:text-brand-300'
                                   : 'text-slate-600 hover:text-ink dark:text-slate-300 dark:hover:text-white' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- ===============================================================
         KPIs (4 cards principais)
         =============================================================== --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        {{-- Total de vagas --}}
        <div class="card !p-4">
            <div class="flex items-center justify-between">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    <x-icon name="briefcase" class="h-5 w-5"/>
                </div>
                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Vagas</span>
            </div>
            <p class="mt-3 font-display text-2xl sm:text-3xl font-bold">{{ $totalJobs }}</p>
            <p class="mt-1 text-[11px] text-slate-500">
                <span class="font-semibold text-emerald-600">{{ $openJobs }}</span> abertas ·
                <span class="font-semibold text-slate-500">{{ $closedJobs }}</span> fechadas
            </p>
        </div>

        {{-- Candidaturas + variação --}}
        <div class="card !p-4">
            <div class="flex items-center justify-between">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                    <x-icon name="user" class="h-5 w-5"/>
                </div>
                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Candidaturas</span>
            </div>
            <p class="mt-3 font-display text-2xl sm:text-3xl font-bold">{{ $appsInRange }}</p>
            <p class="mt-1 flex items-center gap-1 text-[11px]">
                @php $up = $appsVariation >= 0; @endphp
                <span class="inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-semibold
                             {{ $up ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                                   : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' }}">
                    <x-icon name="arrow-up" class="h-3 w-3 {{ $up ? '' : 'rotate-180' }}"/>
                    {{ number_format(abs($appsVariation), 1, ',', '.') }}%
                </span>
                <span class="text-slate-500">vs período anterior</span>
            </p>
        </div>

        {{-- Contratações --}}
        <div class="card !p-4">
            <div class="flex items-center justify-between">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                    <x-icon name="check" class="h-5 w-5"/>
                </div>
                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Contratações</span>
            </div>
            <p class="mt-3 font-display text-2xl sm:text-3xl font-bold">{{ $hiredCount }}</p>
            <p class="mt-1 text-[11px] text-slate-500">
                Taxa de resposta: <span class="font-semibold">{{ number_format($responseRate, 1, ',', '.') }}%</span>
            </p>
        </div>

        {{-- Tempo médio de resposta --}}
        <div class="card !p-4">
            <div class="flex items-center justify-between">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">
                    <x-icon name="sparkles" class="h-5 w-5"/>
                </div>
                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Resposta</span>
            </div>
            <p class="mt-3 font-display text-2xl sm:text-3xl font-bold">
                @if ($avgResponseDays !== null)
                    {{ number_format($avgResponseDays, 1, ',', '.') }}<span class="text-base font-normal text-slate-500">d</span>
                @else
                    —
                @endif
            </p>
            <p class="mt-1 text-[11px] text-slate-500">tempo médio até primeira ação</p>
        </div>
    </div>

    {{-- ===============================================================
         KPIs Testes (linha secundária)
         =============================================================== --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <div class="card !p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Testes enviados</p>
            <p class="mt-2 font-display text-2xl font-bold">{{ $invitesSent }}</p>
        </div>
        <div class="card !p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Testes concluídos</p>
            <p class="mt-2 font-display text-2xl font-bold">{{ $invitesCompleted }}</p>
        </div>
        <div class="card !p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Taxa de aprovação</p>
            <p class="mt-2 font-display text-2xl font-bold">
                {{ number_format($passRate, 1, ',', '.') }}<span class="text-base font-normal text-slate-500">%</span>
            </p>
            <p class="text-[11px] text-slate-500">{{ $passedAttempts }}/{{ $totalAttempts }} tentativas</p>
        </div>
        <div class="card !p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Candidaturas / vaga</p>
            <p class="mt-2 font-display text-2xl font-bold">
                {{ $openJobs > 0 ? number_format($appsInRange / $openJobs, 1, ',', '.') : '0' }}
            </p>
            <p class="text-[11px] text-slate-500">média nas vagas abertas</p>
        </div>
    </div>

    {{-- ===============================================================
         Gráficos — Linha (candidaturas) + Pizza (status)
         =============================================================== --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="card !p-4">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-sm font-bold uppercase tracking-wider text-slate-500">
                    Candidaturas por dia (30d)
                </h3>
            </div>
            @if (array_sum($lineData) === 0)
                <div class="mt-6 flex flex-col items-center justify-center gap-2 py-10 text-center text-slate-400">
                    <x-icon name="briefcase" class="h-8 w-8"/>
                    <p class="text-xs">Sem candidaturas nos últimos 30 dias.</p>
                </div>
            @else
                <div class="relative mt-3 h-64">
                    <canvas id="dash-line" wire:ignore></canvas>
                </div>
            @endif
        </div>

        <div class="card !p-4">
            <h3 class="font-display text-sm font-bold uppercase tracking-wider text-slate-500">
                Distribuição por status
            </h3>
            @if (array_sum($pieData) === 0)
                <div class="mt-6 flex flex-col items-center justify-center gap-2 py-10 text-center text-slate-400">
                    <x-icon name="check" class="h-8 w-8"/>
                    <p class="text-xs">Sem candidaturas no período selecionado.</p>
                </div>
            @else
                <div class="relative mt-3 h-64">
                    <canvas id="dash-pie" wire:ignore></canvas>
                </div>
            @endif
        </div>
    </div>

    {{-- ===============================================================
         Gráficos — Barras (top vagas) + Funil de conversão
         =============================================================== --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="card !p-4">
            <h3 class="font-display text-sm font-bold uppercase tracking-wider text-slate-500">
                Top 5 vagas por candidaturas
            </h3>
            @if (empty($barLabels) || array_sum($barData) === 0)
                <div class="mt-6 flex flex-col items-center justify-center gap-2 py-10 text-center text-slate-400">
                    <x-icon name="briefcase" class="h-8 w-8"/>
                    <p class="text-xs">Nenhuma vaga com candidatos ainda.</p>
                </div>
            @else
                <div class="relative mt-3 h-64">
                    <canvas id="dash-bar" wire:ignore></canvas>
                </div>
            @endif
        </div>

        <div class="card !p-4">
            <h3 class="font-display text-sm font-bold uppercase tracking-wider text-slate-500">
                Funil de conversão
            </h3>
            @php $funnelTop = max(1, $funnel[0]['value']); @endphp
            <div class="mt-4 space-y-3">
                @foreach ($funnel as $step)
                    @php $pct = ($step['value'] / $funnelTop) * 100; @endphp
                    <div>
                        <div class="mb-1 flex items-baseline justify-between text-xs">
                            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $step['label'] }}</span>
                            <span class="font-mono text-slate-500">
                                {{ $step['value'] }}
                                <span class="text-[10px] text-slate-400">
                                    ({{ number_format($pct, 1, ',', '.') }}%)
                                </span>
                            </span>
                        </div>
                        <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full {{ $step['color'] }} transition-all"
                                 style="width: {{ max(2, $pct) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if (($funnel[0]['value'] ?? 0) === 0)
                <p class="mt-4 text-center text-[11px] text-slate-400">
                    Assim que chegarem candidaturas, o funil se preenche automaticamente.
                </p>
            @endif
        </div>
    </div>

    {{-- ===============================================================
         Widgets — Últimas atividades + Vagas em risco
         =============================================================== --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Atividades --}}
        <div class="card !p-4">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-sm font-bold uppercase tracking-wider text-slate-500">
                    Últimas atividades
                </h3>
                <span class="text-[10px] text-slate-400">últimos 30 dias</span>
            </div>

            @if ($activities->isEmpty())
                <div class="mt-6 flex flex-col items-center justify-center gap-2 py-8 text-center text-slate-400">
                    <x-icon name="sparkles" class="h-7 w-7"/>
                    <p class="text-xs">Nenhuma atividade recente ainda.</p>
                </div>
            @else
                <ul class="mt-3 divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($activities as $act)
                        <li class="flex items-start gap-3 py-2.5">
                            <div class="grid h-8 w-8 flex-shrink-0 place-items-center rounded-full bg-slate-100 dark:bg-slate-800 {{ $act['color'] }}">
                                <x-icon :name="$act['icon']" class="h-4 w-4"/>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs sm:text-sm font-medium">{{ $act['title'] }}</p>
                                <p class="truncate text-[11px] text-slate-500">{{ $act['subtitle'] }}</p>
                            </div>
                            <span class="flex-shrink-0 text-[10px] text-slate-400">
                                {{ $act['when']?->diffForHumans(short: true) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Vagas em risco --}}
        <div class="card !p-4">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-sm font-bold uppercase tracking-wider text-slate-500">
                    Vagas em risco
                </h3>
                <span class="text-[10px] text-slate-400">sem tração</span>
            </div>

            @if ($atRiskJobs->isEmpty())
                <div class="mt-6 flex flex-col items-center justify-center gap-2 py-8 text-center text-slate-400">
                    <x-icon name="check" class="h-7 w-7 text-emerald-500"/>
                    <p class="text-xs">Tudo em dia — nenhuma vaga parada.</p>
                </div>
            @else
                <ul class="mt-3 space-y-2">
                    @foreach ($atRiskJobs as $job)
                        @php
                            $daysOld = (int) $job->created_at->diffInDays(now());
                            $noApps  = $job->applications_count === 0;
                        @endphp
                        <li>
                            <a href="{{ route('jobs.show', $job) }}"
                               class="flex items-center justify-between gap-2 rounded-xl p-2 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold">{{ $job->title }}</p>
                                    <p class="text-[11px] text-slate-500">
                                        Aberta há {{ $daysOld }}d ·
                                        @if ($noApps)
                                            <span class="text-rose-600">sem candidatos</span>
                                        @else
                                            {{ $job->applications_count }} candidatos, sem movimentação há 14d+
                                        @endif
                                    </p>
                                </div>
                                <x-icon name="arrow-right" class="h-4 w-4 flex-shrink-0 text-slate-400"/>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- ===============================================================
         Chart.js — carrega e renderiza os 3 gráficos
         =============================================================== --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
    @endpush

    <script>
        (function () {
            const payload = {
                line:  { labels: @json($lineLabels), data: @json($lineData) },
                pie:   { labels: @json($pieLabels),  data: @json($pieData) },
                bar:   { labels: @json($barLabels),  data: @json($barData) },
            };

            const state = { instances: {} };

            function destroyAll() {
                Object.values(state.instances).forEach(c => { try { c.destroy(); } catch (_) {} });
                state.instances = {};
            }

            function render() {
                if (typeof window.Chart === 'undefined') { return; }
                destroyAll();

                const brand = '#22C55E';
                const palette = ['#94a3b8', '#F97316', '#f59e0b', '#8b5cf6', '#10b981', '#f43f5e'];
                const gridColor = document.documentElement.classList.contains('dark') ? '#1e293b' : '#f1f5f9';
                const tickColor = document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b';

                const commonScales = {
                    x: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 } } },
                    y: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 10 } }, beginAtZero: true, precision: 0 },
                };

                const lineEl = document.getElementById('dash-line');
                if (lineEl && payload.line.data.some(v => v > 0)) {
                    state.instances.line = new Chart(lineEl, {
                        type: 'line',
                        data: {
                            labels: payload.line.labels,
                            datasets: [{
                                label: 'Candidaturas',
                                data: payload.line.data,
                                borderColor: brand,
                                backgroundColor: 'rgba(34,197,94,0.12)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 2,
                                pointHoverRadius: 4,
                                borderWidth: 2,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: commonScales,
                        },
                    });
                }

                const pieEl = document.getElementById('dash-pie');
                if (pieEl && payload.pie.data.some(v => v > 0)) {
                    state.instances.pie = new Chart(pieEl, {
                        type: 'doughnut',
                        data: {
                            labels: payload.pie.labels,
                            datasets: [{
                                data: payload.pie.data,
                                backgroundColor: palette,
                                borderWidth: 2,
                                borderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '62%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { color: tickColor, font: { size: 11 }, boxWidth: 10 },
                                },
                            },
                        },
                    });
                }

                const barEl = document.getElementById('dash-bar');
                if (barEl && payload.bar.data.some(v => v > 0)) {
                    state.instances.bar = new Chart(barEl, {
                        type: 'bar',
                        data: {
                            labels: payload.bar.labels,
                            datasets: [{
                                label: 'Candidaturas',
                                data: payload.bar.data,
                                backgroundColor: 'rgba(34,197,94,0.75)',
                                borderRadius: 8,
                                borderSkipped: false,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: { legend: { display: false } },
                            scales: commonScales,
                        },
                    });
                }
            }

            function waitAndRender() {
                if (typeof window.Chart === 'undefined') {
                    return setTimeout(waitAndRender, 60);
                }
                render();
            }

            waitAndRender();

            // Rerrenderiza após updates do Livewire (troca de range, etc.)
            document.addEventListener('livewire:navigated', waitAndRender);
            if (window.Livewire) {
                window.Livewire.hook('morph.updated', waitAndRender);
            }
        })();
    </script>
</div>
