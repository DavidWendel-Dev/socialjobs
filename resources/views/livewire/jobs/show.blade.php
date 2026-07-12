<div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 space-y-4 lg:col-span-8">
        {{-- Header da vaga --}}
        <div class="card">
            <div class="flex items-start gap-4">
                @php
                    $companyName = $job?->companyProfile?->legal_name ?? 'Empresa';
                    $companyLogo = $job?->companyProfile?->logo_path;
                @endphp
                @if ($companyLogo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($companyLogo) }}"
                         alt="{{ $companyName }}"
                         class="h-16 w-16 rounded-2xl object-cover bg-slate-100">
                @else
                    <div class="grid h-16 w-16 place-items-center rounded-2xl bg-brand-100 text-brand-700 font-display text-2xl font-bold">
                        {{ mb_substr($companyName, 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <h1 class="font-display text-2xl font-bold">{{ $job?->title ?? 'Vaga' }}</h1>
                    @if ($job?->companyProfile)
                        <a href="{{ route('profile.company', $job->companyProfile) }}"
                           class="text-sm font-medium text-brand-600 hover:underline">
                            {{ $companyName }}
                        </a>
                    @endif
                    <p class="text-sm text-slate-500">
                        {{ $job?->location ?: 'Sem localização especificada' }}
                        · publicada {{ optional($job?->published_at ?? $job?->created_at)->diffForHumans() }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @if ($job?->modality)
                            <x-chip color="sky">{{ ucfirst($job->modality) }}</x-chip>
                        @endif
                        @if ($job?->contract_type)
                            <x-chip color="emerald">{{ strtoupper($job->contract_type) }}</x-chip>
                        @endif
                        @if ($job?->seniority)
                            <x-chip color="amber">{{ ucfirst($job->seniority) }}</x-chip>
                        @endif
                        @if ($job?->salary_min || $job?->salary_max)
                            <x-chip color="violet">
                                @if ($job->salary_min && $job->salary_max)
                                    R$ {{ number_format((float) $job->salary_min, 0, ',', '.') }} – {{ number_format((float) $job->salary_max, 0, ',', '.') }}
                                @elseif ($job->salary_min)
                                    A partir de R$ {{ number_format((float) $job->salary_min, 0, ',', '.') }}
                                @else
                                    Até R$ {{ number_format((float) $job->salary_max, 0, ',', '.') }}
                                @endif
                            </x-chip>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Descrição --}}
        <div class="card">
            <h3 class="font-display text-lg font-bold">Descrição</h3>
            <div class="prose prose-slate dark:prose-invert mt-3 max-w-none text-sm">
                {!! $job?->description ?: '<p>Sem descrição disponível.</p>' !!}
            </div>
        </div>

        {{-- Skills requeridas --}}
        @if ($job && $job->skills->isNotEmpty())
            <div class="card">
                <h3 class="font-display text-lg font-bold">Skills requeridas</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($job->skills as $skill)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                            {{ $skill->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Sidebar de ações --}}
    <aside class="col-span-12 lg:col-span-4">
        <div class="card sticky top-24 space-y-4">
            @if (! is_null($matchScore))
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-500">Compatibilidade</p>
                    <div class="mt-2 flex items-center gap-4">
                        <div class="relative h-20 w-20">
                            <svg viewBox="0 0 36 36" class="h-20 w-20 -rotate-90">
                                <circle cx="18" cy="18" r="16" fill="none" stroke="currentColor" stroke-width="3"
                                        class="text-slate-200 dark:text-slate-700"/>
                                <circle cx="18" cy="18" r="16" fill="none" stroke="currentColor" stroke-width="3"
                                        stroke-dasharray="{{ round($matchScore * 100.53 / 100, 2) }} 100.53"
                                        class="text-brand-500"/>
                            </svg>
                            <span class="absolute inset-0 grid place-items-center font-display font-bold">
                                {{ $matchScore }}%
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Baseado em skills e preferências.</p>
                    </div>
                </div>
            @endif

            @if ($alreadyApplied)
                <button disabled class="btn-secondary w-full opacity-60">
                    <x-icon name="check" class="mr-1.5 h-4 w-4"/> Candidatura enviada
                </button>
            @else
                <button wire:click="apply" class="btn-primary w-full">
                    ⚡ Candidatar em 1 clique
                </button>
            @endif

            @if (session('status'))
                <p class="rounded-xl bg-brand-50 p-2 text-center text-xs text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                    {{ session('status') }}
                </p>
            @endif

            <div class="flex justify-around border-t border-slate-100 pt-3 dark:border-slate-800">
                @foreach (config('reactions.types', []) as $key => $r)
                    <button title="{{ $r['label'] }}" class="transition hover:scale-125">
                        <x-reaction-icon :type="$key" size="md"/>
                    </button>
                @endforeach
            </div>
        </div>
    </aside>
</div>
