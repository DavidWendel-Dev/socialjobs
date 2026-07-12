<div class="card !p-4">
    <div class="mb-3 flex items-center justify-between">
        <div>
            <h3 class="font-display text-base font-bold">Vagas para você</h3>
            <p class="text-xs text-slate-500">Baseado no seu perfil</p>
        </div>
        <x-icon name="briefcase" class="h-4 w-4 text-brand-500"/>
    </div>

    @if ($jobs->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-xs text-slate-500 dark:border-slate-700">
            Nenhuma vaga compatível no momento.<br>
            Adicione skills no seu perfil para ver recomendações.
        </div>
    @else
        <ul class="space-y-3">
            @foreach ($jobs as $job)
                @php
                    $company = $job->companyProfile?->legal_name ?? 'Empresa';
                    $score   = (int) round($job->match_score ?? 0);
                @endphp
                <li>
                    <a href="{{ route('jobs.show', $job) }}"
                       class="group block rounded-xl border border-slate-100 p-3 transition hover:border-brand-500 hover:bg-brand-50/30 dark:border-slate-800 dark:hover:border-brand-500 dark:hover:bg-brand-500/5">
                        <div class="flex items-start justify-between gap-2">
                            <p class="line-clamp-2 text-sm font-semibold leading-tight group-hover:text-brand-600">
                                {{ $job->title }}
                            </p>
                            @if ($score > 0)
                                <span class="shrink-0 rounded-full bg-brand-500/10 px-1.5 py-0.5 text-[10px] font-bold text-brand-700 dark:text-brand-300">
                                    {{ $score }}%
                                </span>
                            @endif
                        </div>
                        <p class="mt-0.5 truncate text-xs text-slate-500">
                            {{ $company }}
                            @if ($job->location) · {{ $job->location }} @endif
                        </p>
                        <div class="mt-2 flex flex-wrap gap-1">
                            @if ($job->modality)
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ ucfirst($job->modality) }}
                                </span>
                            @endif
                            @if ($job->seniority)
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ ucfirst($job->seniority) }}
                                </span>
                            @endif
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        <a href="{{ route('jobs.index') }}"
           class="mt-3 block rounded-xl bg-slate-50 py-2 text-center text-xs font-medium text-brand-600 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700">
            Ver todas as vagas →
        </a>
    @endif
</div>
