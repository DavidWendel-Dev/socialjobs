<div class="space-y-4">
    {{-- Card resumo --}}
    <div class="card !p-4">
        <div class="flex items-center gap-2">
            <x-icon name="briefcase" class="h-4 w-4 text-brand-500"/>
            <h3 class="font-display text-sm font-bold uppercase tracking-wider text-slate-500">
                Painel da empresa
            </h3>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2">
            <div class="rounded-xl bg-brand-50 p-3 text-center dark:bg-brand-500/10">
                <p class="text-2xl font-display font-bold text-brand-700 dark:text-brand-300">{{ $openJobsCount }}</p>
                <p class="text-[10px] font-medium text-slate-600 dark:text-slate-400">
                    {{ $openJobsCount === 1 ? 'vaga aberta' : 'vagas abertas' }}
                </p>
            </div>
            <div class="rounded-xl bg-amber-50 p-3 text-center dark:bg-amber-500/10">
                <p class="text-2xl font-display font-bold text-amber-700 dark:text-amber-300">{{ $newApplicationsCount }}</p>
                <p class="text-[10px] font-medium text-slate-600 dark:text-slate-400">
                    {{ $newApplicationsCount === 1 ? 'candidato novo' : 'candidatos novos' }}
                </p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3 text-center dark:bg-slate-800/60">
                <p class="text-2xl font-display font-bold text-slate-800 dark:text-slate-200">{{ $inReviewCount }}</p>
                <p class="text-[10px] font-medium text-slate-600 dark:text-slate-400">em análise</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3 text-center dark:bg-slate-800/60">
                <p class="text-2xl font-display font-bold text-slate-800 dark:text-slate-200">{{ $totalJobs }}</p>
                <p class="text-[10px] font-medium text-slate-600 dark:text-slate-400">total publicadas</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-2">
            <a href="{{ route('jobs.create') }}"
               class="inline-flex items-center justify-center gap-1 rounded-xl bg-brand-500 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-600">
                <x-icon name="plus" class="h-3.5 w-3.5"/> Nova vaga
            </a>
            <a href="{{ route('company.kanban') }}"
               class="inline-flex items-center justify-center gap-1 rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-800 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                <x-icon name="check" class="h-3.5 w-3.5"/> Kanban
            </a>
        </div>
    </div>

    {{-- Top vagas --}}
    @if ($topJobs->count())
        <div class="card !p-4">
            <h3 class="font-display text-sm font-bold uppercase tracking-wider text-slate-500">
                Vagas com mais candidatos
            </h3>
            <ul class="mt-3 space-y-2">
                @foreach ($topJobs as $job)
                    <li>
                        <a href="{{ route('jobs.show', $job) }}"
                           class="flex items-center justify-between gap-2 rounded-xl p-2 -mx-2 hover:bg-slate-50 dark:hover:bg-slate-800">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold">{{ $job->title }}</p>
                                <p class="text-[11px] text-slate-500">
                                    {{ $job->applications_count }} {{ $job->applications_count === 1 ? 'candidato' : 'candidatos' }}
                                </p>
                            </div>
                            <x-icon name="arrow-right" class="h-4 w-4 flex-shrink-0 text-slate-400"/>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
