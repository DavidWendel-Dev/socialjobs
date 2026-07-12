<div class="mx-auto max-w-6xl space-y-5">
    {{-- ============================================================
         Header
         ============================================================ --}}
    <div class="rounded-2xl bg-gradient-to-br from-brand-500 via-brand-600 to-accent p-6 text-white sm:p-8">
        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider opacity-90">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2 4 5v7c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V5l-8-3z"/>
            </svg>
            Certificação verificada
        </div>
        <h1 class="mt-1 font-display text-2xl font-bold sm:text-3xl">
            Testes de proficiência
        </h1>
        <p class="mt-2 max-w-2xl text-sm opacity-90">
            Prove suas habilidades e ganhe badges verificados no seu perfil e Currículo Digital.
            Recrutadores confiam mais em quem tem competências <strong>validadas pela plataforma</strong>.
        </p>
    </div>

    {{-- ============================================================
         Filtros
         ============================================================ --}}
    <div class="rounded-2xl bg-white p-4 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative min-w-0 flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="q"
                       placeholder="Buscar por Excel, Vendas, Inglês..."
                       class="input !pl-9">
            </div>

            <select wire:model.live="category" class="input !w-auto">
                <option value="all">Todas as categorias</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>

            <select wire:model.live="difficulty" class="input !w-auto">
                <option value="all">Todas as dificuldades</option>
                <option value="basic">Básico</option>
                <option value="intermediate">Intermediário</option>
                <option value="advanced">Avançado</option>
            </select>

            @if ($q || $category !== 'all' || $difficulty !== 'all')
                <button type="button" wire:click="clearFilters"
                        class="btn-ghost text-xs">Limpar</button>
            @endif
        </div>
    </div>

    {{-- ============================================================
         Grid de testes
         ============================================================ --}}
    @if ($assessments->count())
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($assessments as $a)
                @php
                    $best = $bestScores[$a->id] ?? null;
                    $passed = $best !== null && $best >= $a->passing_score;
                    $colorMap = [
                        'brand'  => 'from-brand-500 to-brand-600',
                        'blue'   => 'from-blue-500 to-blue-600',
                        'amber'  => 'from-amber-500 to-amber-600',
                        'accent' => 'from-accent to-orange-500',
                        'rose'   => 'from-rose-500 to-rose-600',
                    ];
                    $gradient = $colorMap[$a->color] ?? $colorMap['brand'];
                @endphp
                <a href="{{ route('skill-assessments.take', ['slug' => $a->slug]) }}"
                   wire:key="skill-{{ $a->id }}"
                   class="group relative flex flex-col overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-100 transition hover:-translate-y-0.5 hover:shadow-lg dark:bg-slate-900 dark:ring-slate-800">

                    {{-- Faixa colorida topo --}}
                    <div class="relative h-24 bg-gradient-to-br {{ $gradient }}">
                        <div class="absolute inset-0 opacity-25"
                             style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,.6), transparent 40%), radial-gradient(circle at 80% 60%, rgba(255,255,255,.4), transparent 40%);"></div>
                        <div class="absolute left-4 top-4 grid h-12 w-12 place-items-center rounded-xl bg-white/20 backdrop-blur">
                            <x-icon :name="$a->icon" class="h-6 w-6 text-white"/>
                        </div>

                        @if ($passed)
                            <div class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-bold text-brand-700 shadow">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2 4 5v7c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V5l-8-3zm-1 15L7 13l1.4-1.4L11 14.2l4.6-4.6L17 11l-6 6z"/>
                                </svg>
                                {{ $best }}/100
                            </div>
                        @endif
                    </div>

                    {{-- Conteúdo --}}
                    <div class="flex flex-1 flex-col p-4">
                        <div class="flex items-center gap-2 text-[10px] font-medium uppercase tracking-wider text-slate-500">
                            <span>{{ $a->category }}</span>
                            <span>·</span>
                            <span>{{ $a->difficultyLabel() }}</span>
                        </div>
                        <h3 class="mt-1 font-display text-base font-bold leading-tight">
                            {{ $a->title }}
                        </h3>
                        <p class="mt-1 line-clamp-2 text-xs text-slate-500">
                            {{ $a->short_description }}
                        </p>

                        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] text-slate-500 dark:border-slate-800">
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                                {{ $a->duration_minutes }} min
                            </span>
                            <span class="inline-flex items-center gap-1 font-semibold text-brand-600">
                                +{{ $a->xp_reward }} XP
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-900">
            <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                <x-icon name="sparkles" class="h-6 w-6"/>
            </div>
            <p class="font-semibold">Nenhum teste encontrado</p>
            <p class="mt-1 text-sm text-slate-500">Tente ajustar os filtros ou limpar a busca.</p>
        </div>
    @endif
</div>
