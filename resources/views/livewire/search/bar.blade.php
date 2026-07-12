<div class="relative w-full max-w-md"
     x-data="{ open: @entangle('open') }"
     @click.outside="open = false"
     @keydown.escape.window="open = false">

    {{-- Input --}}
    <form wire:submit.prevent="goToResults" class="relative">
        <x-icon name="search"
                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"/>

        <input type="search"
               wire:model.live.debounce.250ms="q"
               @focus="if ($wire.q.trim() !== '') open = true"
               @keydown.arrow-down.prevent="$el.blur(); document.querySelector('#search-panel a')?.focus()"
               placeholder="Buscar pessoas, empresas, vagas..."
               autocomplete="off"
               class="w-full rounded-full border-slate-200 bg-slate-100 pl-10 pr-10 text-sm focus:border-brand-500 focus:bg-white focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-800 dark:focus:bg-slate-900">

        @if (trim($q) !== '')
            <button type="button"
                    wire:click="clear"
                    class="absolute right-2 top-1/2 grid h-6 w-6 -translate-y-1/2 place-items-center rounded-full text-slate-400 hover:bg-slate-200 hover:text-ink dark:hover:bg-slate-700 dark:hover:text-white"
                    aria-label="Limpar busca">
                <x-icon name="x" class="h-3.5 w-3.5"/>
            </button>
        @endif
    </form>

    {{-- Dropdown de resultados --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-cloak
         id="search-panel"
         class="absolute left-0 right-0 top-full z-30 mt-2 max-h-[70vh] overflow-y-auto rounded-2xl bg-white p-2 shadow-soft-lg ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700">

        {{-- Loading indicator (aparece durante debounce) --}}
        <div wire:loading.flex wire:target="q" class="items-center gap-2 p-3 text-xs text-slate-500">
            <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".3"/>
                <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
            </svg>
            Buscando...
        </div>

        <div wire:loading.remove wire:target="q">
            @if (mb_strlen($term) < 2)
                <p class="p-4 text-center text-xs text-slate-500">
                    Digite pelo menos 2 caracteres para buscar.
                </p>
            @elseif (! $hasResults)
                <div class="p-4 text-center">
                    <p class="text-sm font-medium">Nada encontrado para "{{ $term }}"</p>
                    <p class="mt-1 text-xs text-slate-500">
                        Tente outra palavra ou pesquise no diretório completo.
                    </p>
                    <a href="{{ route('search') }}?q={{ urlencode($term) }}"
                       wire:navigate
                       @click="open = false"
                       class="btn-secondary mt-3 !py-1.5 text-xs">
                        Ver página de busca
                    </a>
                </div>
            @else
                {{-- Pessoas --}}
                @if ($people->count())
                    <div class="mb-1">
                        <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Pessoas</p>
                        @foreach ($people as $u)
                            @php $uname = $u->username ?? $u->id; @endphp
                            <a href="{{ url('/u/' . $uname) }}"
                               wire:navigate
                               @click="open = false"
                               class="flex items-center gap-3 rounded-xl p-2 hover:bg-slate-50 focus:bg-slate-50 focus:outline-none dark:hover:bg-slate-800 dark:focus:bg-slate-800">
                                <x-avatar :user="$u" size="sm"/>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">{{ $u->name }}</p>
                                    <p class="truncate text-xs text-slate-500">
                                        {{ $u->headline ?: '@' . $uname }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Empresas --}}
                @if ($companies->count())
                    <div class="mb-1 border-t border-slate-100 pt-1 dark:border-slate-800">
                        <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Empresas</p>
                        @foreach ($companies as $c)
                            @php
                                $cp = $c->companyProfile;
                                // Logo: prioriza avatar_path do user (padrão novo). Fallback: logo_path do CompanyProfile (legado).
                                $logoUrl = $c->avatar_path
                                    ? $c->avatar_url
                                    : ($cp?->logo_path ? \Illuminate\Support\Facades\Storage::url($cp->logo_path) : null);
                                $displayName = $cp?->trade_name ?: ($cp?->legal_name ?: $c->name);
                            @endphp
                            <a href="{{ $cp ? route('profile.company', $cp) : '#' }}"
                               wire:navigate
                               @click="open = false"
                               class="flex items-center gap-3 rounded-xl p-2 hover:bg-slate-50 focus:bg-slate-50 focus:outline-none dark:hover:bg-slate-800 dark:focus:bg-slate-800">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}"
                                         alt="" class="h-8 w-8 rounded-lg object-cover bg-slate-100">
                                @else
                                    <div class="grid h-8 w-8 place-items-center rounded-lg bg-brand-100 text-xs font-bold text-brand-700">
                                        {{ mb_substr($displayName ?? 'C', 0, 1) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">{{ $displayName }}</p>
                                    <p class="text-xs text-slate-500">Empresa</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Vagas --}}
                @if ($jobs->count())
                    <div class="mb-1 border-t border-slate-100 pt-1 dark:border-slate-800">
                        <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Vagas</p>
                        @foreach ($jobs as $job)
                            <a href="{{ route('jobs.show', $job) }}"
                               wire:navigate
                               @click="open = false"
                               class="flex items-center gap-3 rounded-xl p-2 hover:bg-slate-50 focus:bg-slate-50 focus:outline-none dark:hover:bg-slate-800 dark:focus:bg-slate-800">
                                <div class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                                    <x-icon name="briefcase" class="h-4 w-4"/>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">{{ $job->title }}</p>
                                    <p class="truncate text-xs text-slate-500">
                                        {{ $job->companyProfile?->trade_name ?: ($job->companyProfile?->legal_name ?? 'Empresa') }}
                                        @if ($job->location) · {{ $job->location }} @endif
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Cursos --}}
                @if ($courses->count())
                    <div class="mb-1 border-t border-slate-100 pt-1 dark:border-slate-800">
                        <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Cursos</p>
                        @foreach ($courses as $co)
                            <a href="{{ route('courses.show', $co) }}"
                               wire:navigate
                               @click="open = false"
                               class="flex items-center gap-3 rounded-xl p-2 hover:bg-slate-50 focus:bg-slate-50 focus:outline-none dark:hover:bg-slate-800 dark:focus:bg-slate-800">
                                <div class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-accent/10 text-accent">
                                    <x-icon name="academic" class="h-4 w-4"/>
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
                @endif

                {{-- Posts --}}
                @if ($posts->count())
                    <div class="mb-1 border-t border-slate-100 pt-1 dark:border-slate-800">
                        <p class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Publicações</p>
                        @foreach ($posts as $p)
                            <a href="{{ route('posts.show', $p) }}"
                               wire:navigate
                               @click="open = false"
                               class="flex items-start gap-3 rounded-xl p-2 hover:bg-slate-50 focus:bg-slate-50 focus:outline-none dark:hover:bg-slate-800 dark:focus:bg-slate-800">
                                <x-avatar :user="$p->user" size="sm"/>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold">
                                        {{ $p->user?->name ?? 'Usuário' }}
                                        <span class="font-normal text-slate-500">
                                            · {{ optional($p->created_at)->diffForHumans() }}
                                        </span>
                                    </p>
                                    <p class="line-clamp-2 text-xs text-slate-600 dark:text-slate-300">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($p->body), 120) }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Rodapé com "Ver todos os resultados" --}}
                <a href="{{ route('search') }}?q={{ urlencode($term) }}"
                   wire:navigate
                   @click="open = false"
                   class="mt-2 flex items-center justify-center gap-1.5 rounded-xl border-t border-slate-100 py-2 text-xs font-semibold text-brand-600 hover:bg-brand-50 dark:border-slate-800 dark:hover:bg-brand-500/10">
                    Ver todos os resultados
                    <x-icon name="arrow-right" class="h-3 w-3"/>
                </a>
            @endif
        </div>
    </div>
</div>
