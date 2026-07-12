<div class="grid grid-cols-12 gap-4">
    {{-- Sidebar de filtros --}}
    <aside class="col-span-12 lg:col-span-4">
        <div class="card sticky top-24 space-y-3">
            <h3 class="font-display text-lg font-bold">Filtros</h3>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Buscar</label>
                <input type="text" wire:model.live.debounce.400ms="q"
                       placeholder="Título ou palavra-chave..." class="input">
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Senioridade</label>
                <select wire:model.live="seniority" class="input">
                    <option value="">Todas</option>
                    @foreach ($seniorityLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Modalidade</label>
                <select wire:model.live="modality" class="input">
                    <option value="">Todas</option>
                    @foreach ($modalityLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Tipo de contrato</label>
                <select wire:model.live="contract_type" class="input">
                    <option value="">Todos</option>
                    @foreach ($contractLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Localização</label>
                <input type="text" wire:model.live.debounce.400ms="location"
                       placeholder="Ex: São Paulo, SP" class="input">
            </div>

            <button wire:click="clearFilters" class="btn-ghost w-full">Limpar filtros</button>
        </div>
    </aside>

    {{-- Lista de vagas --}}
    <div class="col-span-12 space-y-3 lg:col-span-8">
        <div class="flex items-baseline justify-between px-1">
            <p class="text-sm text-slate-500">
                <strong class="text-ink dark:text-white">{{ $jobs->total() }}</strong>
                {{ $jobs->total() === 1 ? 'vaga encontrada' : 'vagas encontradas' }}
            </p>
        </div>

        @forelse ($jobs as $job)
            @php
                $match = null;
                if ($matcher && auth()->check()) {
                    $match = (int) round($matcher->scoreFor(auth()->user(), $job));
                }
                $companyName = $job->companyProfile?->legal_name ?? 'Empresa';
                $companyLogo = $job->companyProfile?->logo_path;
            @endphp
            <a href="{{ route('jobs.show', $job) }}"
               class="card block transition hover:-translate-y-0.5 hover:shadow-soft-lg">
                <div class="flex items-start gap-3">
                    @if ($companyLogo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($companyLogo) }}"
                             alt="{{ $companyName }}"
                             class="h-12 w-12 rounded-2xl object-cover bg-slate-100">
                    @else
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-100 text-brand-700 font-display font-bold">
                            {{ mb_substr($companyName, 0, 1) }}
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h3 class="truncate font-semibold">{{ $job->title }}</h3>
                                <p class="truncate text-xs text-slate-500">
                                    {{ $companyName }}
                                    @if ($job->location) · {{ $job->location }} @endif
                                </p>
                            </div>
                            @if (! is_null($match))
                                <span class="shrink-0 rounded-full bg-brand-500/10 px-2 py-0.5 text-[11px] font-bold text-brand-700 dark:text-brand-300">
                                    {{ $match }}% match
                                </span>
                            @endif
                        </div>

                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @if ($job->modality)
                                <x-chip color="sky">{{ $modalityLabels[$job->modality] ?? ucfirst($job->modality) }}</x-chip>
                            @endif
                            @if ($job->contract_type)
                                <x-chip color="emerald">{{ $contractLabels[$job->contract_type] ?? strtoupper($job->contract_type) }}</x-chip>
                            @endif
                            @if ($job->seniority)
                                <x-chip color="amber">{{ $seniorityLabels[$job->seniority] ?? ucfirst($job->seniority) }}</x-chip>
                            @endif
                            @if ($job->salary_min || $job->salary_max)
                                <x-chip color="violet">
                                    @if ($job->salary_min && $job->salary_max)
                                        R$ {{ number_format((float) $job->salary_min, 0, ',', '.') }} – {{ number_format((float) $job->salary_max, 0, ',', '.') }}
                                    @elseif ($job->salary_min)
                                        A partir de R$ {{ number_format((float) $job->salary_min, 0, ',', '.') }}
                                    @endif
                                </x-chip>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="card text-center">
                <p class="text-sm text-slate-500">Nenhuma vaga encontrada com esses filtros.</p>
                <button wire:click="clearFilters" class="btn-secondary mt-3">Limpar filtros</button>
            </div>
        @endforelse

        @if (method_exists($jobs, 'links'))
            <div>{{ $jobs->links() }}</div>
        @endif
    </div>
</div>
