<div class="mx-auto max-w-6xl space-y-5">
    {{-- Header --}}
    <div class="rounded-2xl bg-gradient-to-br from-brand-500 via-brand-600 to-accent p-6 text-white sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider opacity-90">
                    <x-icon name="academic" class="h-3.5 w-3.5"/>
                    Cursos internos
                </div>
                <h1 class="mt-1 font-display text-2xl font-bold sm:text-3xl">
                    Meus cursos
                </h1>
                <p class="mt-2 max-w-2xl text-xs sm:text-sm opacity-90">
                    Crie cursos privados de onboarding e treinamento para seus candidatos e colaboradores. Distribua por link de convite.
                </p>
            </div>
            <a href="{{ route('company.courses.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/95 px-4 py-2.5 text-xs sm:text-sm font-bold text-brand-700 shadow-soft hover:bg-white">
                <x-icon name="sparkles" class="h-4 w-4"/>
                Criar novo curso
            </a>
        </div>
    </div>

    {{-- Lista de cursos --}}
    @if ($courses->count())
        <div class="grid gap-3 sm:gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $c)
                <div wire:key="cc-{{ $c->id }}"
                     class="group relative flex flex-col overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-100 transition hover:shadow-lg dark:bg-slate-900 dark:ring-slate-800">
                    {{-- Cabeçalho colorido / thumb --}}
                    <div class="relative h-24 bg-gradient-to-br from-brand-500 to-accent">
                        @if ($c->thumbnail_path)
                            <img src="{{ $c->thumbnail_path }}" alt=""
                                 class="absolute inset-0 h-full w-full object-cover opacity-80">
                        @endif
                        <div class="absolute inset-0 bg-black/20"></div>
                        <div class="absolute left-3 top-3 grid h-10 w-10 place-items-center rounded-xl bg-white/20 backdrop-blur">
                            <x-icon name="academic" class="h-5 w-5 text-white"/>
                        </div>
                        <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-bold text-brand-700 shadow">
                            {{ $c->visibility === 'invite_only' ? 'Só por convite' : 'Público' }}
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-4">
                        <div class="flex items-center gap-2 text-[10px] font-medium uppercase tracking-wider text-slate-500">
                            <span>{{ $c->category ?: 'Interno' }}</span>
                            <span>·</span>
                            <span>
                                @switch($c->level)
                                    @case('beginner') Iniciante @break
                                    @case('intermediate') Intermediário @break
                                    @case('advanced') Avançado @break
                                    @default {{ ucfirst($c->level) }}
                                @endswitch
                            </span>
                        </div>
                        <h3 class="mt-1 font-display text-base font-bold leading-tight">
                            {{ $c->title }}
                        </h3>
                        <p class="mt-1 line-clamp-2 text-xs text-slate-500">
                            {{ $c->summary ?: 'Sem descrição curta.' }}
                        </p>

                        {{-- Estatísticas --}}
                        <div class="mt-3 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3 text-center text-[11px] dark:border-slate-800">
                            <div>
                                <div class="font-display text-base font-bold text-brand-600">{{ $c->modules_count }}</div>
                                <p class="text-slate-500">Módulos</p>
                            </div>
                            <div>
                                <div class="font-display text-base font-bold text-brand-600">{{ $c->enrollments_count }}</div>
                                <p class="text-slate-500">Matriculados</p>
                            </div>
                        </div>

                        {{-- Ações --}}
                        <div class="mt-3 flex flex-wrap items-center gap-1.5 border-t border-slate-100 pt-3 dark:border-slate-800">
                            <a href="{{ route('company.courses.enrollments', $c) }}"
                               class="flex-1 rounded-xl bg-brand-500 px-2.5 py-1.5 text-center text-[11px] font-semibold text-white hover:bg-brand-600">
                                Matrículas
                            </a>
                            <a href="{{ route('company.courses.edit', $c) }}"
                               class="rounded-xl border border-slate-200 px-2.5 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                               title="Editar">
                                <x-icon name="pencil" class="h-3.5 w-3.5"/>
                            </a>
                            <button type="button"
                                    wire:click="delete({{ $c->id }})"
                                    wire:confirm="Excluir este curso? Módulos, aulas e matrículas relacionadas serão removidos."
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
            <p class="font-semibold">Você ainda não criou nenhum curso interno</p>
            <p class="mt-1 text-sm text-slate-500">Monte um onboarding ou treinamento personalizado da sua empresa em minutos.</p>
            <a href="{{ route('company.courses.create') }}"
               class="mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-bold text-white hover:bg-brand-600">
                <x-icon name="sparkles" class="h-4 w-4"/>
                Criar meu primeiro curso
            </a>
        </div>
    @endif
</div>
