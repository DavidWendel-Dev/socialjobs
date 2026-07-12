<div class="mx-auto max-w-6xl space-y-5">
    {{-- ============================================================
         Header
         ============================================================ --}}
    <div class="rounded-2xl bg-gradient-to-br from-brand-500 via-brand-600 to-accent p-6 text-white sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider opacity-90">
                    <x-icon name="academic" class="h-3.5 w-3.5"/>
                    Testes da empresa
                </div>
                <h1 class="mt-1 font-display text-2xl font-bold sm:text-3xl">
                    Meus testes
                </h1>
                <p class="mt-2 max-w-2xl text-xs sm:text-sm opacity-90">
                    Crie testes customizados para avaliar candidatos, envie por convite e receba o resultado com selo anti-cola.
                </p>
            </div>
            <a href="{{ route('company.assessments.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/95 px-4 py-2.5 text-xs sm:text-sm font-bold text-brand-700 shadow-soft hover:bg-white">
                <x-icon name="sparkles" class="h-4 w-4"/>
                Criar teste com IA
            </a>
        </div>
    </div>

    {{-- ============================================================
         Lista de testes
         ============================================================ --}}
    @if ($assessments->count())
        <div class="grid gap-3 sm:gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($assessments as $a)
                @php
                    $colorMap = [
                        'brand'  => 'from-brand-500 to-brand-600',
                        'blue'   => 'from-blue-500 to-blue-600',
                        'amber'  => 'from-amber-500 to-amber-600',
                        'accent' => 'from-accent to-orange-500',
                        'rose'   => 'from-rose-500 to-rose-600',
                    ];
                    $gradient = $colorMap[$a->color] ?? $colorMap['brand'];
                @endphp
                <div wire:key="ca-{{ $a->id }}"
                     class="group relative flex flex-col overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-100 transition hover:shadow-lg dark:bg-slate-900 dark:ring-slate-800">
                    {{-- Faixa colorida topo --}}
                    <div class="relative h-20 bg-gradient-to-br {{ $gradient }}">
                        <div class="absolute inset-0 opacity-25"
                             style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,.6), transparent 40%), radial-gradient(circle at 80% 60%, rgba(255,255,255,.4), transparent 40%);"></div>
                        <div class="absolute left-3 top-3 grid h-10 w-10 place-items-center rounded-xl bg-white/20 backdrop-blur">
                            <x-icon :name="$a->icon" class="h-5 w-5 text-white"/>
                        </div>
                        <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-bold text-brand-700 shadow">
                            {{ $a->visibility === 'invite_only' ? 'Só por convite' : 'Público' }}
                        </span>
                    </div>

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

                        {{-- Estatísticas --}}
                        <div class="mt-3 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3 text-center text-[11px] dark:border-slate-800">
                            <div>
                                <div class="font-display text-base font-bold text-brand-600">{{ $a->invitations_count }}</div>
                                <p class="text-slate-500">Convites</p>
                            </div>
                            <div>
                                <div class="font-display text-base font-bold text-brand-600">{{ $a->attempts_count }}</div>
                                <p class="text-slate-500">Respondidos</p>
                            </div>
                        </div>

                        {{-- Ações --}}
                        <div class="mt-3 flex flex-wrap items-center gap-1.5 border-t border-slate-100 pt-3 dark:border-slate-800">
                            <a href="{{ route('company.assessments.results', $a) }}"
                               class="flex-1 rounded-xl bg-brand-500 px-2.5 py-1.5 text-center text-[11px] font-semibold text-white hover:bg-brand-600">
                                Resultados
                            </a>
                            <a href="{{ route('company.assessments.edit', $a) }}"
                               class="rounded-xl border border-slate-200 px-2.5 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                               title="Editar">
                                <x-icon name="pencil" class="h-3.5 w-3.5"/>
                            </a>
                            <button type="button"
                                    wire:click="delete({{ $a->id }})"
                                    wire:confirm="Excluir este teste? Convites e tentativas relacionadas serão removidos."
                                    class="rounded-xl border border-rose-200 px-2.5 py-1.5 text-[11px] font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:hover:bg-rose-500/10"
                                    title="Excluir">
                                <x-icon name="x" class="h-3.5 w-3.5"/>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-900">
            <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                <x-icon name="academic" class="h-6 w-6"/>
            </div>
            <p class="font-semibold">Você ainda não criou nenhum teste</p>
            <p class="mt-1 text-sm text-slate-500">Crie um teste customizado usando IA em menos de 1 minuto.</p>
            <a href="{{ route('company.assessments.create') }}"
               class="mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-bold text-white hover:bg-brand-600">
                <x-icon name="sparkles" class="h-4 w-4"/>
                Criar meu primeiro teste
            </a>
        </div>
    @endif
</div>
