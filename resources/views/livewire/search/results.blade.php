<div class="space-y-4">
    {{-- ============================================================
         Cabeçalho — input de busca em destaque
         ============================================================ --}}
    <div class="card">
        <form wire:submit.prevent="$refresh" class="relative">
            <x-icon name="search"
                    class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"/>
            <input type="search"
                   wire:model.live.debounce.350ms="q"
                   placeholder="Buscar pessoas, empresas, vagas, cursos ou posts..."
                   autofocus
                   autocomplete="off"
                   class="input !pl-12 !py-3 !text-base">
        </form>

        @if (mb_strlen($term) >= 2)
            <p class="mt-3 text-sm text-slate-500">
                <strong class="text-ink dark:text-white">{{ $totalCount }}</strong>
                {{ $totalCount === 1 ? 'resultado encontrado' : 'resultados encontrados' }}
                para
                <strong class="text-brand-600">"{{ $term }}"</strong>
            </p>
        @endif
    </div>

    @if (mb_strlen($term) < 2)
        {{-- Estado inicial: convite --}}
        <div class="card text-center">
            <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-brand-500/10 text-brand-600">
                <x-icon name="search" class="h-6 w-6"/>
            </div>
            <h2 class="font-display text-lg font-bold">O que você está procurando?</h2>
            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                Digite pelo menos 2 caracteres para buscar em toda a plataforma.
                Você pode encontrar pessoas, empresas, vagas, cursos e publicações.
            </p>
        </div>
    @elseif ($totalCount === 0)
        {{-- Nenhum resultado --}}
        <div class="card text-center">
            <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800">
                <x-icon name="x" class="h-6 w-6"/>
            </div>
            <h2 class="font-display text-lg font-bold">Nada encontrado</h2>
            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                Não achamos nada para <strong>"{{ $term }}"</strong>. Tente uma palavra diferente
                ou remova filtros.
            </p>
        </div>
    @else
        {{-- ============================================================
             Filtros por tipo (chips)
             ============================================================ --}}
        <div class="card !p-2">
            <div class="flex flex-wrap gap-1">
                @php
                    $types = [
                        'all'       => ['Tudo',        $totalCount],
                        'people'    => ['Pessoas',     $counts['people']],
                        'companies' => ['Empresas',    $counts['companies']],
                        'jobs'      => ['Vagas',       $counts['jobs']],
                        'courses'   => ['Cursos',      $counts['courses']],
                        'posts'     => ['Publicações', $counts['posts']],
                    ];
                @endphp
                @foreach ($types as $key => [$label, $count])
                    <button type="button"
                            wire:click="setType('{{ $key }}')"
                            class="flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-sm font-medium transition
                                   {{ $type === $key
                                       ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900'
                                       : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        {{ $label }}
                        @if ($count > 0)
                            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none
                                         {{ $type === $key
                                             ? 'bg-white/20 text-white dark:bg-slate-900/20 dark:text-slate-900'
                                             : 'bg-slate-100 text-slate-500 dark:bg-slate-800' }}">
                                {{ $count }}
                            </span>
                        @endif
                    </button>
                @endforeach

                {{-- Ordenação --}}
                <div class="ml-auto flex items-center gap-2 pl-2 pr-1">
                    <label class="text-xs text-slate-500">Ordenar:</label>
                    <select wire:model.live="sort"
                            class="rounded-lg border-slate-200 bg-slate-50 py-1 pl-2 pr-8 text-xs dark:border-slate-700 dark:bg-slate-800">
                        <option value="relevance">Mais recentes</option>
                        <option value="oldest">Mais antigos</option>
                        <option value="name">Alfabético</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ============================================================
             Resultados
             ============================================================ --}}

        {{-- === Pessoas === --}}
        @if (($type === 'all' || $type === 'people') && $results['people']->count())
            <section class="card">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold">Pessoas</h3>
                    @if ($type === 'all' && $counts['people'] > $results['people']->count())
                        <button type="button" wire:click="setType('people')"
                                class="text-xs font-medium text-brand-600 hover:underline">
                            Ver todos ({{ $counts['people'] }}) →
                        </button>
                    @endif
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($results['people'] as $u)
                        @php $uname = $u->username ?? $u->id; @endphp
                        <a href="{{ url('/u/' . $uname) }}"
                           wire:navigate
                           class="flex items-center gap-3 rounded-xl border border-slate-100 p-3 transition hover:border-brand-500 hover:bg-brand-50/30 dark:border-slate-800 dark:hover:border-brand-500 dark:hover:bg-brand-500/5">
                            <x-avatar :user="$u" size="md"/>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $u->name }}</p>
                                <p class="truncate text-xs text-slate-500">
                                    {{ $u->headline ?: '@' . $uname }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
                @if ($type === 'people' && method_exists($results['people'], 'links'))
                    <div class="mt-4">{{ $results['people']->links() }}</div>
                @endif
            </section>
        @endif

        {{-- === Empresas === --}}
        @if (($type === 'all' || $type === 'companies') && $results['companies']->count())
            <section class="card">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold">Empresas</h3>
                    @if ($type === 'all' && $counts['companies'] > $results['companies']->count())
                        <button type="button" wire:click="setType('companies')"
                                class="text-xs font-medium text-brand-600 hover:underline">
                            Ver todas ({{ $counts['companies'] }}) →
                        </button>
                    @endif
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($results['companies'] as $c)
                        @php
                            $cp = $c->companyProfile;
                            $logoUrl = $c->avatar_path
                                ? $c->avatar_url
                                : ($cp?->logo_path ? \Illuminate\Support\Facades\Storage::url($cp->logo_path) : null);
                            $displayName = $cp?->trade_name ?: ($cp?->legal_name ?: $c->name);
                        @endphp
                        <a href="{{ $cp ? route('profile.company', $cp) : '#' }}"
                           wire:navigate
                           class="flex items-center gap-3 rounded-xl border border-slate-100 p-3 transition hover:border-brand-500 hover:bg-brand-50/30 dark:border-slate-800">
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}"
                                     alt="" class="h-10 w-10 rounded-lg object-cover bg-slate-100">
                            @else
                                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-100 text-lg font-bold text-brand-700">
                                    {{ mb_substr($displayName ?? 'C', 0, 1) }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $displayName }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $cp?->industry ?? 'Empresa' }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
                @if ($type === 'companies' && method_exists($results['companies'], 'links'))
                    <div class="mt-4">{{ $results['companies']->links() }}</div>
                @endif
            </section>
        @endif

        {{-- === Vagas === --}}
        @if (($type === 'all' || $type === 'jobs') && $results['jobs']->count())
            <section class="card">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold">Vagas</h3>
                    @if ($type === 'all' && $counts['jobs'] > $results['jobs']->count())
                        <button type="button" wire:click="setType('jobs')"
                                class="text-xs font-medium text-brand-600 hover:underline">
                            Ver todas ({{ $counts['jobs'] }}) →
                        </button>
                    @endif
                </div>
                <ul class="space-y-2">
                    @foreach ($results['jobs'] as $job)
                        <li>
                            <a href="{{ route('jobs.show', $job) }}"
                               wire:navigate
                               class="flex items-start gap-3 rounded-xl border border-slate-100 p-3 transition hover:border-brand-500 hover:bg-brand-50/30 dark:border-slate-800">
                                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                                    <x-icon name="briefcase" class="h-5 w-5"/>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">{{ $job->title }}</p>
                                    <p class="truncate text-xs text-slate-500">
                                        {{ $job->companyProfile?->trade_name ?: ($job->companyProfile?->legal_name ?? 'Empresa') }}
                                        @if ($job->location) · {{ $job->location }} @endif
                                    </p>
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        @if ($job->modality)
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600 dark:bg-slate-800">
                                                {{ ucfirst($job->modality) }}
                                            </span>
                                        @endif
                                        @if ($job->seniority)
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600 dark:bg-slate-800">
                                                {{ ucfirst($job->seniority) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
                @if ($type === 'jobs' && method_exists($results['jobs'], 'links'))
                    <div class="mt-4">{{ $results['jobs']->links() }}</div>
                @endif
            </section>
        @endif

        {{-- === Cursos === --}}
        @if (($type === 'all' || $type === 'courses') && $results['courses']->count())
            <section class="card">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold">Cursos</h3>
                    @if ($type === 'all' && $counts['courses'] > $results['courses']->count())
                        <button type="button" wire:click="setType('courses')"
                                class="text-xs font-medium text-brand-600 hover:underline">
                            Ver todos ({{ $counts['courses'] }}) →
                        </button>
                    @endif
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($results['courses'] as $co)
                        <a href="{{ route('courses.show', $co) }}"
                           wire:navigate
                           class="flex items-start gap-3 rounded-xl border border-slate-100 p-3 transition hover:border-brand-500 hover:bg-brand-50/30 dark:border-slate-800">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-accent/10 text-accent">
                                <x-icon name="academic" class="h-5 w-5"/>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $co->title }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $co->level ? ucfirst($co->level) : 'Curso' }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
                @if ($type === 'courses' && method_exists($results['courses'], 'links'))
                    <div class="mt-4">{{ $results['courses']->links() }}</div>
                @endif
            </section>
        @endif

        {{-- === Posts === --}}
        @if (($type === 'all' || $type === 'posts') && $results['posts']->count())
            <section class="card">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold">Publicações</h3>
                    @if ($type === 'all' && $counts['posts'] > $results['posts']->count())
                        <button type="button" wire:click="setType('posts')"
                                class="text-xs font-medium text-brand-600 hover:underline">
                            Ver todas ({{ $counts['posts'] }}) →
                        </button>
                    @endif
                </div>
                <ul class="space-y-3">
                    @foreach ($results['posts'] as $p)
                        <li>
                            <a href="{{ route('posts.show', $p) }}"
                               wire:navigate
                               class="flex items-start gap-3 rounded-xl border border-slate-100 p-3 transition hover:border-brand-500 hover:bg-brand-50/30 dark:border-slate-800">
                                <x-avatar :user="$p->user" size="sm"/>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold">
                                        {{ $p->user?->name ?? 'Usuário' }}
                                        <span class="font-normal text-slate-500">
                                            · {{ optional($p->created_at)->diffForHumans() }}
                                        </span>
                                    </p>
                                    <p class="mt-0.5 line-clamp-2 text-sm text-slate-700 dark:text-slate-200">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($p->body), 200) }}
                                    </p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
                @if ($type === 'posts' && method_exists($results['posts'], 'links'))
                    <div class="mt-4">{{ $results['posts']->links() }}</div>
                @endif
            </section>
        @endif
    @endif
</div>
