<div class="mx-auto max-w-6xl space-y-5" x-data="{ filtersOpen: false }">
    {{-- ============================================================
         Header (hero)
         ============================================================ --}}
    <div class="rounded-2xl bg-gradient-to-br from-brand-500 via-brand-600 to-accent p-6 text-white sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider opacity-90">
                    <x-icon name="user" class="h-3.5 w-3.5"/>
                    Banco de talentos
                </div>
                <h1 class="mt-1 font-display text-2xl font-bold sm:text-3xl">Buscar candidatos</h1>
                <p class="mt-2 max-w-2xl text-xs sm:text-sm opacity-90">
                    Encontre profissionais ativos, filtre por skills, localização e testes aprovados, e entre em contato direto.
                </p>
            </div>

            {{-- Toggle de filtros no mobile --}}
            <button type="button"
                    @click="filtersOpen = !filtersOpen"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/95 px-4 py-2.5 text-xs sm:text-sm font-bold text-brand-700 shadow-soft hover:bg-white lg:hidden">
                <x-icon name="menu" class="h-4 w-4"/>
                <span x-text="filtersOpen ? 'Ocultar filtros' : 'Mostrar filtros'">Filtros</span>
            </button>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-2xl bg-emerald-50 p-3 text-sm text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-12">
        {{-- ============================================================
             SIDEBAR — Filtros
             ============================================================ --}}
        <aside class="lg:col-span-4 xl:col-span-3"
               x-show="filtersOpen || window.matchMedia('(min-width: 1024px)').matches"
               x-transition
               x-cloak>
            <div class="card sticky top-24 space-y-4 !p-4">
                {{-- Busca livre --}}
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Buscar
                    </label>
                    <div class="relative mt-1">
                        <input type="text"
                               wire:model.live.debounce.500ms="search"
                               placeholder="Nome, usuário ou headline..."
                               class="input w-full pl-9">
                        <x-icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"/>
                    </div>
                </div>

                {{-- Skills --}}
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Habilidades
                    </label>

                    @if (!empty($skillNames))
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($skillNames as $sn)
                                <button type="button"
                                        wire:click="toggleSkill(@js($sn))"
                                        class="inline-flex items-center gap-1 rounded-full bg-brand-100 px-2.5 py-1 text-xs font-medium text-brand-700 hover:bg-brand-200 dark:bg-brand-500/20 dark:text-brand-300">
                                    {{ $sn }}
                                    <x-icon name="x" class="h-3 w-3"/>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="relative mt-2" x-data="{ open: false }" @click.outside="open = false">
                        <input type="text"
                               wire:model.live.debounce.300ms="skillQuery"
                               @focus="open = true"
                               @input="open = true"
                               placeholder="Ex: Laravel, React..."
                               class="input w-full">

                        @if ($skillSuggestions && count($skillSuggestions) > 0)
                            <ul x-show="open"
                                x-transition
                                class="absolute z-20 mt-1 max-h-56 w-full overflow-y-auto rounded-xl bg-white p-1 shadow-soft-lg ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700"
                                x-cloak>
                                @foreach ($skillSuggestions as $s)
                                    <li>
                                        <button type="button"
                                                wire:click="toggleSkill(@js($s->name))"
                                                @click="open = false"
                                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-800">
                                            <x-icon name="plus" class="h-3.5 w-3.5 text-brand-500"/>
                                            {{ $s->name }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                {{-- Cidade / UF --}}
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Localização
                    </label>
                    <div class="mt-1 grid grid-cols-3 gap-2">
                        <input type="text"
                               wire:model.live.debounce.500ms="city"
                               placeholder="Cidade"
                               class="input col-span-2 w-full">
                        <input type="text"
                               wire:model.live.debounce.500ms="uf"
                               maxlength="2"
                               placeholder="UF"
                               class="input w-full uppercase">
                    </div>
                </div>

                {{-- Checkboxes --}}
                <div class="space-y-2">
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input type="checkbox"
                               wire:model.live="openToWork"
                               class="h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                            Aberto a trabalhar
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input type="checkbox"
                               wire:model.live="hasResume"
                               class="h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                        Tem CV completo
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input type="checkbox"
                               wire:model.live="hasAssessments"
                               class="h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                        Fez testes de proficiência
                    </label>
                </div>

                {{-- Mínimo de testes aprovados --}}
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Mínimo de testes aprovados:
                        <span class="text-brand-600">{{ $minAssessmentsPassed }}</span>
                    </label>
                    <input type="range"
                           min="0" max="10" step="1"
                           wire:model.live.debounce.400ms="minAssessmentsPassed"
                           class="mt-2 w-full accent-brand-500">
                </div>

                {{-- Experiência --}}
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Experiência
                    </label>
                    <select wire:model.live="experienceYears"
                            class="input mt-1 w-full">
                        <option value="any">Qualquer</option>
                        <option value="junior">Júnior (&lt; 2 anos)</option>
                        <option value="pleno">Pleno (2–5 anos)</option>
                        <option value="senior">Sênior (5+ anos)</option>
                    </select>
                </div>

                {{-- Ordenação --}}
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Ordenar por
                    </label>
                    <select wire:model.live="sortBy"
                            class="input mt-1 w-full">
                        <option value="recent">Mais recentes</option>
                        <option value="name">Nome (A-Z)</option>
                        <option value="assessments_desc">Mais testes aprovados</option>
                    </select>
                </div>

                <button type="button"
                        wire:click="resetFilters"
                        class="btn-secondary w-full">
                    <x-icon name="x" class="h-4 w-4"/>
                    Limpar filtros
                </button>
            </div>
        </aside>

        {{-- ============================================================
             RESULTADOS
             ============================================================ --}}
        <section class="lg:col-span-8 xl:col-span-9">
            <div class="mb-3 flex items-center justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    <span class="font-semibold text-ink dark:text-white">{{ $candidates->total() }}</span>
                    {{ $candidates->total() === 1 ? 'candidato encontrado' : 'candidatos encontrados' }}
                </p>
                <div class="flex items-center gap-2 text-xs text-slate-500"
                     wire:loading
                     wire:target="search,skillNames,city,uf,openToWork,hasResume,hasAssessments,minAssessmentsPassed,experienceYears,sortBy">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".3"/>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    Buscando...
                </div>
            </div>

            @if ($candidates->count() === 0)
                {{-- Empty state --}}
                <div class="card flex flex-col items-center justify-center gap-3 !p-10 text-center">
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                        <x-icon name="search" class="h-6 w-6"/>
                    </span>
                    <h3 class="font-display text-lg font-bold">Nenhum candidato encontrado</h3>
                    <p class="max-w-md text-sm text-slate-500">
                        Tente reduzir a quantidade de filtros ou usar termos mais amplos. Você também pode limpar
                        tudo e começar de novo.
                    </p>
                    <button type="button" wire:click="resetFilters" class="btn-secondary mt-2">
                        <x-icon name="x" class="h-4 w-4"/>
                        Limpar filtros
                    </button>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($candidates as $u)
                        @php
                            $profile   = $u->candidateProfile;
                            $topSkills = $profile?->skills?->take(5) ?? collect();
                            $passedCount = (int) ($u->passed_assessments_count ?? 0);
                        @endphp
                        <div wire:key="talent-{{ $u->id }}"
                             class="card flex flex-col gap-3 !p-4 transition hover:shadow-lg">
                            <div class="flex items-start gap-3">
                                <x-avatar :user="$u" size="lg" class="!h-14 !w-14 shrink-0"/>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <h3 class="truncate font-display text-base font-bold">
                                                {{ $u->name }}
                                            </h3>
                                            <p class="truncate text-xs text-slate-500">{{ '@' . ($u->username ?? $u->id) }}</p>
                                        </div>
                                        @if ($u->open_to_work)
                                            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Aberto
                                            </span>
                                        @endif
                                    </div>
                                    @if ($u->headline)
                                        <p class="mt-1 line-clamp-2 text-xs text-slate-600 dark:text-slate-300">
                                            {{ $u->headline }}
                                        </p>
                                    @endif
                                    @if ($u->location)
                                        <p class="mt-1 flex items-center gap-1 text-[11px] text-slate-500">
                                            <x-icon name="home" class="h-3 w-3"/>
                                            {{ $u->location }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Skills --}}
                            @if ($topSkills->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($topSkills as $sk)
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                            {{ $sk->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Badges --}}
                            <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                @if ($passedCount > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-brand-100 px-2 py-0.5 font-bold text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                                        <x-icon name="check" class="h-3 w-3"/>
                                        {{ $passedCount }} {{ $passedCount === 1 ? 'teste' : 'testes' }} {{ $passedCount === 1 ? 'aprovado' : 'aprovados' }}
                                    </span>
                                @endif
                                @if ($profile?->resume_path)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2 py-0.5 font-medium text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">
                                        <x-icon name="book" class="h-3 w-3"/>
                                        CV
                                    </span>
                                @endif
                                @if ($u->is_verified)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-2 py-0.5 font-medium text-violet-700 dark:bg-violet-500/20 dark:text-violet-300">
                                        <x-icon name="check" class="h-3 w-3"/>
                                        Verificado
                                    </span>
                                @endif
                            </div>

                            {{-- Ações --}}
                            <div class="mt-auto grid grid-cols-3 gap-2 pt-2">
                                <a href="{{ route('profile.candidate', ['user' => $u->username ?? $u->id]) }}"
                                   target="_blank"
                                   class="btn-secondary !py-1.5 !text-xs">
                                    <x-icon name="user" class="h-3.5 w-3.5"/>
                                    Perfil
                                </a>
                                <button type="button"
                                        wire:click="sendMessage({{ $u->id }})"
                                        class="btn-secondary !py-1.5 !text-xs">
                                    <x-icon name="message" class="h-3.5 w-3.5"/>
                                    Mensagem
                                </button>
                                <button type="button"
                                        wire:click="inviteToTest({{ $u->id }})"
                                        class="btn-primary !py-1.5 !text-xs">
                                    <x-icon name="academic" class="h-3.5 w-3.5"/>
                                    Convidar
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $candidates->links() }}
                </div>
            @endif
        </section>
    </div>

    {{-- ============================================================
         MODAL — Convidar para teste
         ============================================================ --}}
    @if ($showInviteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4"
             x-data
             @keydown.escape.window="$wire.closeInviteModal()">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                 wire:click="closeInviteModal"></div>

            <div class="relative z-10 w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-soft-lg dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="grid h-8 w-8 place-items-center rounded-xl bg-brand-100 text-brand-600 dark:bg-brand-500/20 dark:text-brand-300">
                            <x-icon name="academic" class="h-4 w-4"/>
                        </span>
                        <h3 class="font-display text-base font-bold">Convidar para teste</h3>
                    </div>
                    <button type="button"
                            wire:click="closeInviteModal"
                            class="grid h-8 w-8 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <x-icon name="x" class="h-4 w-4"/>
                    </button>
                </div>

                <div class="space-y-4 px-5 py-4">
                    @if ($companyAssessments->isEmpty())
                        <div class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20">
                            Você ainda não tem testes ativos.
                            <a href="{{ route('company.assessments.create') }}"
                               class="font-semibold underline hover:no-underline">
                                Criar um teste agora
                            </a>
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Escolha o teste
                            </label>
                            <select wire:model="inviteAssessmentId" class="input mt-1 w-full">
                                <option value="">— Selecione —</option>
                                @foreach ($companyAssessments as $a)
                                    <option value="{{ $a->id }}">{{ $a->title }}</option>
                                @endforeach
                            </select>
                            @error('inviteAssessmentId')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                                E-mail do candidato
                            </label>
                            <input type="email"
                                   wire:model="inviteEmail"
                                   class="input mt-1 w-full"
                                   placeholder="candidato@email.com">
                            @error('inviteEmail')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-3 dark:border-slate-800 dark:bg-slate-800/40">
                    <button type="button"
                            wire:click="closeInviteModal"
                            class="btn-secondary !py-1.5 !text-xs">
                        Cancelar
                    </button>
                    <button type="button"
                            wire:click="sendInvite"
                            wire:loading.attr="disabled"
                            @disabled($companyAssessments->isEmpty())
                            class="btn-primary !py-1.5 !text-xs">
                        <x-icon name="arrow-right" class="h-3.5 w-3.5"/>
                        Enviar convite
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
