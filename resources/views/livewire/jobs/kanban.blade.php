<div class="space-y-4">
    {{-- ============================================================
         Header
         ============================================================ --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold">Pipeline de candidatos</h1>
            <p class="text-xs text-slate-500">Arraste os cards entre as colunas ou use as ações rápidas.</p>
        </div>
        <a href="{{ route('jobs.create') }}" class="btn-primary">
            <x-icon name="plus" class="h-4 w-4"/> Nova vaga
        </a>
    </div>

    @if (session('status'))
        <div class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    {{-- ============================================================
         Barra de filtros
         ============================================================ --}}
    <div class="card !p-3">
        <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Buscar por nome</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-2 top-2.5 h-4 w-4 text-slate-400"/>
                    <input type="text"
                           wire:model.live.debounce.500ms="search"
                           placeholder="Ex.: Maria Silva"
                           class="input pl-8">
                </div>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Vaga</label>
                <select wire:model.live="jobFilter" class="input">
                    <option value="">Todas as vagas</option>
                    @foreach ($jobs as $job)
                        <option value="{{ $job->id }}">{{ $job->title }} @if($job->status !== 'open') · {{ $job->status }} @endif</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Skill</label>
                <input type="text"
                       wire:model.live.debounce.500ms="skillFilter"
                       placeholder="Ex.: Laravel, React..."
                       class="input">
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Ordenar</label>
                <select wire:model.live="orderBy" class="input">
                    <option value="recent">Mais recentes</option>
                    <option value="score">Melhor score</option>
                </select>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-3 dark:border-slate-800">
            <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-200">
                <input type="checkbox" wire:model.live="onlyPassed"
                       class="rounded border-slate-300 text-brand-500 focus:ring-brand-400">
                Só com testes aprovados
            </label>

            @if ($search || $jobFilter || $skillFilter || $onlyPassed || $orderBy !== 'recent')
                <button type="button"
                        wire:click="clearFilters"
                        class="text-xs font-semibold text-brand-600 hover:underline">
                    Limpar filtros
                </button>
            @endif

            {{-- Seletor mobile de coluna --}}
            <div class="ml-auto flex items-center gap-2 md:hidden">
                <label class="text-[11px] font-semibold uppercase text-slate-500">Coluna:</label>
                <select wire:model.live="mobileColumn" class="input !py-1 !text-xs">
                    @foreach ($columns as $key => $label)
                        <option value="{{ $key }}">{{ $label }} ({{ $groups[$key]->count() }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ============================================================
         Colunas Kanban
         ============================================================ --}}
    <div wire:key="kanban-board-{{ $orderBy }}-{{ $jobFilter }}-{{ $onlyPassed ? 1 : 0 }}"
         class="grid grid-cols-1 gap-3 md:grid-cols-3 lg:grid-cols-6"
         x-data
         x-init="
            (async () => {
                if (!window.Sortable) {
                    await new Promise(r => { let s=document.createElement('script'); s.src='https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js'; s.onload=r; document.head.appendChild(s); });
                }
                document.querySelectorAll('[data-kanban-col]').forEach(col => {
                    if (col.__sortableBound) return;
                    col.__sortableBound = true;
                    new window.Sortable(col, {
                        group: 'kanban',
                        animation: 150,
                        ghostClass: 'opacity-40',
                        onEnd: (evt) => {
                            const id = evt.item.dataset.id;
                            const status = evt.to.dataset.kanbanCol;
                            $wire.updateStatus(parseInt(id), status);
                        }
                    });
                });
            })();
         ">
        @foreach ($columns as $key => $label)
            @php
                $count = $groups[$key]->count();
                $colColor = [
                    'received'  => 'bg-slate-400',
                    'reviewing' => 'bg-sky-500',
                    'interview' => 'bg-violet-500',
                    'offer'     => 'bg-amber-500',
                    'hired'     => 'bg-emerald-500',
                    'rejected'  => 'bg-rose-500',
                ][$key] ?? 'bg-slate-400';
            @endphp
            <div class="card !p-3 {{ $mobileColumn === $key ? '' : 'hidden md:block' }}">
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full {{ $colColor }}"></span>
                        <h3 class="text-sm font-semibold">{{ $label }}</h3>
                    </div>
                    <span class="chip">{{ $count }}</span>
                </div>

                <div data-kanban-col="{{ $key }}"
                     class="min-h-[120px] space-y-2">
                    @foreach ($groups[$key] as $app)
                        @php
                            $u        = $app->user;
                            $cp       = $u?->candidateProfile;
                            $skills   = $cp?->skills?->take(5) ?? collect();
                            $passed   = (int) ($app->passed_attempts_count ?? 0);
                            $invited  = (int) ($app->invited_count ?? 0);
                        @endphp
                        <div wire:key="app-card-{{ $app->id }}"
                             data-id="{{ $app->id }}"
                             class="group relative cursor-grab rounded-xl bg-slate-50 p-3 text-xs ring-1 ring-slate-100 transition hover:ring-brand-300 dark:bg-slate-800 dark:ring-slate-700">
                            {{-- Nome da vaga (badge) --}}
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="chip !bg-brand-50 !text-brand-700 !py-0.5 !px-2 !text-[10px] dark:!bg-brand-500/10 dark:!text-brand-300 truncate">
                                    <x-icon name="briefcase" class="h-3 w-3"/>
                                    <span class="truncate max-w-[100px]">{{ $app->jobListing?->title ?? 'Vaga' }}</span>
                                </span>
                                <button type="button"
                                        wire:click.stop="openDetails({{ $app->id }})"
                                        class="grid h-6 w-6 place-items-center rounded-lg text-slate-400 hover:bg-white hover:text-brand-600 dark:hover:bg-slate-700"
                                        title="Ver detalhes">
                                    <x-icon name="search" class="h-3.5 w-3.5"/>
                                </button>
                            </div>

                            {{-- Cabeçalho candidato --}}
                            <div class="flex items-start gap-2">
                                <x-avatar :user="$u" size="sm" class="shrink-0"/>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink dark:text-slate-100">
                                        {{ $u->name ?? 'Candidato' }}
                                    </p>
                                    @if(!empty($u?->headline))
                                        <p class="truncate text-[11px] text-slate-500">{{ $u->headline }}</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Chips de skills --}}
                            @if($skills->count())
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($skills as $s)
                                        <span class="rounded-md bg-white px-1.5 py-0.5 text-[10px] font-medium text-slate-600 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700">
                                            {{ $s->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Badges (testes + convidado) --}}
                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                @if($passed > 0)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"
                                          title="Testes aprovados">
                                        <x-icon name="check" class="h-3 w-3"/> {{ $passed }} teste{{ $passed > 1 ? 's' : '' }}
                                    </span>
                                @endif
                                @if($invited > 0)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-violet-100 px-1.5 py-0.5 text-[10px] font-bold text-violet-700 dark:bg-violet-500/15 dark:text-violet-300">
                                        <x-icon name="sparkles" class="h-3 w-3"/> Convidado
                                    </span>
                                @endif
                                @if(!empty($app->internal_note))
                                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"
                                          title="Tem nota interna">
                                        <x-icon name="pencil" class="h-3 w-3"/>
                                    </span>
                                @endif
                            </div>

                            {{-- Footer: data + ações rápidas --}}
                            <div class="mt-2 flex items-center justify-between border-t border-slate-200 pt-2 dark:border-slate-700">
                                <span class="text-[10px] text-slate-500">
                                    {{ $app->created_at?->diffForHumans() ?? '' }}
                                </span>
                                <div class="flex items-center gap-1">
                                    @if(isset($nextStatus[$key]))
                                        <button type="button"
                                                wire:click.stop="moveNext({{ $app->id }})"
                                                title="Mover para {{ $columns[$nextStatus[$key]] ?? '' }}"
                                                class="grid h-6 w-6 place-items-center rounded-md bg-brand-500 text-white hover:bg-brand-600">
                                            <x-icon name="arrow-right" class="h-3 w-3"/>
                                        </button>
                                    @endif
                                    @if($key !== 'rejected' && $key !== 'hired')
                                        <button type="button"
                                                wire:click.stop="quickReject({{ $app->id }})"
                                                title="Rejeitar"
                                                class="grid h-6 w-6 place-items-center rounded-md bg-rose-100 text-rose-600 hover:bg-rose-200 dark:bg-rose-500/15 dark:text-rose-300">
                                            <x-icon name="x" class="h-3 w-3"/>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($count === 0)
                        <p class="py-6 text-center text-[11px] text-slate-400">Nenhum candidato aqui.</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ============================================================
         Modal de detalhes
         ============================================================ --}}
    @if ($showModal && $selected)
        @php
            $u  = $selected->user;
            $cp = $u?->candidateProfile;
            $endorsementsCounts = $selected->endorsements_counts ?? collect();
            $wasInvited = (bool) ($selected->was_invited ?? false);
            $attempts = $selected->attempts ?? collect();
            $currentStatus = $selected->status;
        @endphp
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 p-0 backdrop-blur-sm sm:items-center sm:p-4"
             wire:click.self="closeModal">
            <div class="flex max-h-[95vh] w-full flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl dark:bg-slate-900 sm:max-w-3xl sm:rounded-2xl">

                {{-- Header --}}
                <div class="relative border-b border-slate-100 p-5 dark:border-slate-800">
                    <button type="button" wire:click="closeModal"
                            class="absolute right-3 top-3 grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <x-icon name="x" class="h-4 w-4"/>
                    </button>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                        <x-avatar :user="$u" size="lg" class="shrink-0"/>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-display text-xl font-bold">{{ $u->name ?? 'Candidato' }}</h2>
                                @if($wasInvited)
                                    <span class="chip !bg-violet-100 !text-violet-700 dark:!bg-violet-500/15 dark:!text-violet-300">
                                        <x-icon name="sparkles" class="h-3 w-3"/> Convidado
                                    </span>
                                @endif
                                <span class="chip">Status: {{ $columns[$currentStatus] ?? $currentStatus }}</span>
                            </div>
                            @if(!empty($u?->headline))
                                <p class="text-sm text-slate-600 dark:text-slate-300">{{ $u->headline }}</p>
                            @endif
                            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                @if(!empty($u?->location))
                                    <span>📍 {{ $u->location }}</span>
                                @endif
                                @if(!empty($selected->jobListing?->title))
                                    <span>💼 {{ $selected->jobListing->title }}</span>
                                @endif
                                <span>🕒 candidatou-se {{ $selected->created_at?->diffForHumans() }}</span>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @if(!empty($u?->username))
                                    <a href="{{ route('profile.candidate', ['user' => $u->username]) }}"
                                       target="_blank"
                                       class="text-xs font-semibold text-brand-600 hover:underline">
                                        Ver perfil público →
                                    </a>
                                    <a href="{{ route('cv.public', ['username' => $u->username]) }}"
                                       target="_blank"
                                       class="text-xs font-semibold text-brand-600 hover:underline">
                                        Currículo digital →
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabs --}}
                <div class="border-b border-slate-100 dark:border-slate-800">
                    <div class="scrollbar-none flex gap-1 overflow-x-auto px-3">
                        @foreach([
                            'about'      => 'Sobre',
                            'experience' => 'Experiência',
                            'education'  => 'Educação',
                            'skills'     => 'Skills',
                            'tests'      => 'Testes',
                            'portfolio'  => 'Portfolio',
                            'notes'      => 'Notas',
                        ] as $tabKey => $tabLabel)
                            <button type="button"
                                    wire:click="setTab('{{ $tabKey }}')"
                                    class="whitespace-nowrap border-b-2 px-3 py-2 text-xs font-semibold transition
                                        {{ $activeTab === $tabKey
                                            ? 'border-brand-500 text-brand-600'
                                            : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-200' }}">
                                {{ $tabLabel }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Conteúdo das abas --}}
                <div class="flex-1 overflow-y-auto p-5 text-sm">
                    @if ($activeTab === 'about')
                        <div class="space-y-3">
                            <h3 class="font-semibold">Bio</h3>
                            @if(!empty($cp?->bio))
                                <p class="whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $cp->bio }}</p>
                            @else
                                <p class="text-slate-400">Este candidato ainda não escreveu uma bio.</p>
                            @endif

                            @if(!empty($selected->cover_letter))
                                <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800">
                                    <h4 class="mb-1 text-xs font-semibold uppercase text-slate-500">Carta de apresentação</h4>
                                    <p class="whitespace-pre-line text-slate-700 dark:text-slate-300">{{ $selected->cover_letter }}</p>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-2 text-xs">
                                @if(!empty($cp?->linkedin_url))
                                    <a href="{{ $cp->linkedin_url }}" target="_blank" class="rounded-lg bg-slate-100 px-3 py-2 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700">🔗 LinkedIn</a>
                                @endif
                                @if(!empty($cp?->github_url))
                                    <a href="{{ $cp->github_url }}" target="_blank" class="rounded-lg bg-slate-100 px-3 py-2 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700">🐙 GitHub</a>
                                @endif
                                @if(!empty($cp?->portfolio_url))
                                    <a href="{{ $cp->portfolio_url }}" target="_blank" class="rounded-lg bg-slate-100 px-3 py-2 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700">🌐 Portfolio</a>
                                @endif
                            </div>
                        </div>

                    @elseif ($activeTab === 'experience')
                        @php $experiences = $cp?->experiences ?? collect(); @endphp
                        @if($experiences->count())
                            <ul class="space-y-4">
                                @foreach($experiences as $xp)
                                    <li class="border-l-2 border-brand-500 pl-3">
                                        <p class="font-semibold">{{ $xp->role }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $xp->company_name }} ·
                                            {{ $xp->start_date?->format('m/Y') }} —
                                            {{ $xp->current ? 'atual' : ($xp->end_date?->format('m/Y') ?? '') }}
                                        </p>
                                        @if(!empty($xp->description))
                                            <p class="mt-1 whitespace-pre-line text-xs text-slate-700 dark:text-slate-300">{{ $xp->description }}</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-slate-400">Nenhuma experiência cadastrada.</p>
                        @endif

                    @elseif ($activeTab === 'education')
                        @php $educations = $cp?->educations ?? collect(); @endphp
                        @if($educations->count())
                            <ul class="space-y-3">
                                @foreach($educations as $ed)
                                    <li class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800">
                                        <p class="font-semibold">{{ $ed->degree }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $ed->institution }} ·
                                            {{ $ed->start_date?->format('Y') }} — {{ $ed->end_date?->format('Y') ?? 'em curso' }}
                                        </p>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-slate-400">Nenhuma formação cadastrada.</p>
                        @endif

                    @elseif ($activeTab === 'skills')
                        @php $skills = $cp?->skills ?? collect(); @endphp
                        @if($skills->count())
                            <div class="flex flex-wrap gap-2">
                                @foreach($skills as $s)
                                    @php $endor = (int) ($endorsementsCounts[$s->id] ?? 0); @endphp
                                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium dark:bg-slate-800">
                                        {{ $s->name }}
                                        @if($endor > 0)
                                            <span class="rounded-full bg-brand-500 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $endor }}</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-400">Nenhuma skill cadastrada.</p>
                        @endif

                    @elseif ($activeTab === 'tests')
                        @if($attempts->count())
                            <ul class="space-y-2">
                                @foreach($attempts as $att)
                                    <li class="flex items-center justify-between rounded-xl bg-slate-50 p-3 dark:bg-slate-800">
                                        <div>
                                            <p class="font-semibold">{{ $att->assessment?->title ?? 'Teste' }}</p>
                                            <p class="text-xs text-slate-500">{{ $att->finished_at?->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-bold {{ $att->passed ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $att->score }}%
                                            </p>
                                            <p class="text-[10px] font-semibold uppercase {{ $att->passed ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $att->passed ? 'Aprovado' : 'Reprovado' }}
                                            </p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-slate-400">Este candidato ainda não realizou testes.</p>
                        @endif

                    @elseif ($activeTab === 'portfolio')
                        @php $items = $cp?->portfolioItems ?? collect(); @endphp
                        @if($items->count())
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach($items as $it)
                                    <a href="{{ $it->url }}" target="_blank"
                                       class="rounded-xl bg-slate-50 p-3 transition hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700">
                                        <p class="font-semibold">{{ $it->title }}</p>
                                        @if(!empty($it->description))
                                            <p class="mt-1 text-xs text-slate-500">{{ Str::limit($it->description, 100) }}</p>
                                        @endif
                                        <p class="mt-1 truncate text-[10px] text-brand-600">{{ $it->url }}</p>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-400">Nenhum item no portfolio.</p>
                        @endif

                    @elseif ($activeTab === 'notes')
                        <div x-data="{ saved: false }"
                             x-on:note-saved.window="saved = true; setTimeout(() => saved = false, 1500)">
                            <label class="mb-1 flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-300">
                                <span>Notas internas sobre este candidato</span>
                                <span x-show="saved" x-cloak class="text-[10px] text-emerald-600">✓ Salvo</span>
                            </label>
                            <textarea wire:model.live.debounce.800ms="noteDraft"
                                      rows="8"
                                      class="input font-mono text-xs"
                                      placeholder="Ex.: forte em backend, precisa validar inglês..."></textarea>
                            <p class="mt-1 text-[10px] text-slate-400">Salva automaticamente. Apenas sua empresa vê estas notas.</p>
                        </div>
                    @endif
                </div>

                {{-- Footer sticky --}}
                <div class="border-t border-slate-100 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                    @if ($showRejectPrompt)
                        <div class="mb-2 rounded-xl bg-rose-50 p-3 dark:bg-rose-500/10">
                            <label class="mb-1 block text-xs font-semibold text-rose-700 dark:text-rose-300">
                                Mensagem opcional ao rejeitar
                            </label>
                            <textarea wire:model="rejectMessage" rows="3" class="input text-xs"
                                      placeholder="Obrigado pelo interesse! Nesta oportunidade decidimos seguir com outro perfil..."></textarea>
                            <div class="mt-2 flex justify-end gap-2">
                                <button type="button" wire:click="toggleRejectPrompt"
                                        class="rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                                    Cancelar
                                </button>
                                <button type="button" wire:click="confirmReject"
                                        class="rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-rose-700">
                                    Confirmar rejeição
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center justify-end gap-2">
                        @if ($currentStatus === 'received')
                            <button type="button" wire:click="actMoveTo('reviewing')"
                                    class="btn-secondary !py-2 !px-3 !text-xs">
                                Mover pra Em análise
                            </button>
                        @endif

                        @if (in_array($currentStatus, ['received', 'reviewing']))
                            <button type="button" wire:click="actMoveTo('interview')"
                                    class="btn-secondary !py-2 !px-3 !text-xs">
                                Chamar pra entrevista
                            </button>
                        @endif

                        @if ($currentStatus !== 'hired')
                            <button type="button" wire:click="actMoveTo('hired')"
                                    class="rounded-2xl bg-emerald-500 px-3 py-2 text-xs font-bold text-white shadow-soft hover:bg-emerald-600">
                                Aprovar / Contratar
                            </button>
                        @endif

                        @if ($currentStatus !== 'rejected')
                            <button type="button" wire:click="toggleRejectPrompt"
                                    class="rounded-2xl bg-rose-100 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-200 dark:bg-rose-500/15 dark:text-rose-300">
                                Rejeitar
                            </button>
                        @endif

                        {{-- Dropdown "Enviar teste" --}}
                        @if ($companyTests->count())
                            <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                                <button type="button" x-on:click="open = !open"
                                        class="btn-primary !py-2 !px-3 !text-xs">
                                    <x-icon name="sparkles" class="h-3.5 w-3.5"/>
                                    Enviar teste
                                    <x-icon name="chevron-down" class="h-3.5 w-3.5"/>
                                </button>
                                <div x-show="open" x-cloak
                                     x-transition
                                     class="absolute bottom-full right-0 mb-2 max-h-64 w-64 overflow-y-auto rounded-xl bg-white p-1 shadow-2xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                                    @foreach ($companyTests as $t)
                                        <button type="button"
                                                x-on:click="open = false"
                                                wire:click="sendAssessment({{ $t->id }})"
                                                class="block w-full rounded-lg px-3 py-2 text-left text-xs hover:bg-slate-100 dark:hover:bg-slate-700">
                                            {{ $t->title }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ route('company.assessments.create') }}"
                               class="text-xs font-semibold text-slate-500 hover:text-brand-600">
                                + Criar teste
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
