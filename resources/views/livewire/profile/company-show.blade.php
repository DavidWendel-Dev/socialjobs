<div class="space-y-4 sm:space-y-6">

    {{-- ============================================================
         HERO — Capa + Logo + Info + Ações
         Mobile: layout vertical compacto. Desktop: horizontal expansivo.
         ============================================================ --}}
    <section class="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">

        {{-- CAPA --}}
        <div class="group/cover relative h-32 sm:h-48 md:h-64">
            @if ($user?->cover_path)
                <img src="{{ $user->cover_url }}"
                     alt="Capa de {{ $profile?->legal_name }}"
                     class="h-full w-full object-cover">
            @else
                <div class="h-full w-full bg-gradient-to-br from-brand-500 via-brand-600 to-accent-500"></div>
            @endif
            {{-- Overlay sutil pra melhor contraste na parte de baixo --}}
            <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/30 to-transparent"></div>

            {{-- HOVER: trocar capa (só dono) --}}
            @if ($isOwner)
                {{-- Badge permanente no canto (sempre visível — indica que dá pra clicar) --}}
                <div class="pointer-events-none absolute right-3 top-3 z-10 inline-flex items-center gap-1.5 rounded-full bg-black/60 px-3 py-1.5 text-xs font-semibold text-white shadow-soft backdrop-blur-sm">
                    <x-icon name="sparkles" class="h-3.5 w-3.5"/>
                    <span class="hidden sm:inline">Clique para trocar capa</span>
                    <span class="sm:hidden">Trocar</span>
                </div>

                <label class="absolute inset-0 flex cursor-pointer items-center justify-center bg-black/0 opacity-0 transition group-hover/cover:bg-black/40 group-hover/cover:opacity-100">
                    <input type="file" wire:model="coverUpload" accept="image/*" class="hidden">
                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-soft">
                        <x-icon name="sparkles" class="h-4 w-4"/>
                        <span wire:loading.remove wire:target="coverUpload">Trocar capa</span>
                        <span wire:loading wire:target="coverUpload">Enviando...</span>
                    </span>
                </label>
                <div wire:loading wire:target="coverUpload"
                     class="absolute inset-0 flex items-center justify-center bg-black/40">
                    <span class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-soft">
                        Enviando capa...
                    </span>
                </div>
            @endif
        </div>
        @error('coverUpload')
            <p class="bg-rose-50 px-4 py-2 text-xs text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">
                {{ $message }}
            </p>
        @enderror

        {{-- CORPO DO HERO --}}
        <div class="relative px-4 pb-4 sm:px-6 sm:pb-6">
            @error('avatarUpload')
                <p class="mb-2 rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">
                    {{ $message }}
                </p>
            @enderror

            @if (session('status'))
                <p class="mb-2 rounded-lg bg-brand-50 px-3 py-2 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                    ✓ {{ session('status') }}
                </p>
            @endif

            {{-- Bloco superior: logo + nome + ações --}}
            <div class="flex flex-col gap-4">

                {{-- Linha 1: Logo (saliente) + Ações (canto superior direito) --}}
                <div class="flex items-start justify-between gap-3">
                    {{-- Logo (avatar do user) — só ele passa por cima da capa --}}
                    <div class="group/logo relative -mt-14 flex-shrink-0 sm:-mt-20">
                        @if ($user?->avatar_path)
                            <img src="{{ $user->avatar_url }}"
                                 alt="Logo de {{ $profile?->legal_name }}"
                                 class="h-24 w-24 sm:h-32 sm:w-32 rounded-2xl border-4 border-white bg-white object-cover shadow-soft-lg dark:border-slate-900">
                        @else
                            <div class="grid h-24 w-24 sm:h-32 sm:w-32 place-items-center rounded-2xl border-4 border-white bg-gradient-to-br from-brand-100 to-brand-200 text-4xl sm:text-5xl font-display font-bold text-brand-700 shadow-soft-lg dark:border-slate-900">
                                {{ mb_substr($profile?->legal_name ?? 'E', 0, 1) }}
                            </div>
                        @endif

                        {{-- HOVER: trocar logo (só dono) --}}
                        @if ($isOwner)
                            <label class="absolute inset-0 flex cursor-pointer items-center justify-center rounded-2xl border-4 border-transparent bg-black/0 opacity-0 transition group-hover/logo:bg-black/50 group-hover/logo:opacity-100">
                                <input type="file" wire:model="avatarUpload" accept="image/*" class="hidden">
                                <span class="flex flex-col items-center gap-1 text-white text-[10px] sm:text-xs font-semibold">
                                    <x-icon name="sparkles" class="h-5 w-5 sm:h-6 sm:w-6"/>
                                    <span class="text-center leading-tight">
                                        <span wire:loading.remove wire:target="avatarUpload">Trocar logo</span>
                                        <span wire:loading wire:target="avatarUpload">Enviando...</span>
                                    </span>
                                </span>
                            </label>
                            <div wire:loading wire:target="avatarUpload"
                                 class="absolute inset-0 flex items-center justify-center rounded-2xl bg-black/60 text-xs font-semibold text-white">
                                Enviando...
                            </div>

                            {{-- Botão flutuante SEMPRE visível (mobile + desktop) — indica que dá pra clicar --}}
                            <label class="absolute -bottom-1 -right-1 grid h-9 w-9 sm:h-10 sm:w-10 cursor-pointer place-items-center rounded-full bg-brand-500 text-white shadow-soft-lg ring-2 ring-white transition hover:scale-110 hover:bg-brand-600 dark:ring-slate-900"
                                   title="Alterar logo">
                                <x-icon name="sparkles" class="h-4 w-4 sm:h-5 sm:w-5"/>
                                <input type="file" wire:model="avatarUpload" accept="image/*" class="hidden">
                            </label>
                        @endif
                    </div>

                    {{-- Ações (topo do hero) --}}
                    <div class="flex flex-shrink-0 items-center gap-2 pt-3 sm:pt-4">
                        @if ($isOwner)
                            <a href="{{ route('company.edit') }}"
                               class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-500 px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-soft hover:bg-brand-600 whitespace-nowrap">
                                <x-icon name="sparkles" class="h-4 w-4"/>
                                <span class="hidden sm:inline">Editar perfil</span>
                                <span class="sm:hidden">Editar</span>
                            </a>
                            <a href="{{ route('jobs.create') }}"
                               class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-white px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-slate-800 ring-1 ring-slate-200 hover:bg-slate-50 whitespace-nowrap dark:bg-slate-800 dark:text-slate-100 dark:ring-slate-700 dark:hover:bg-slate-700">
                                <x-icon name="plus" class="h-4 w-4"/>
                                <span class="hidden sm:inline">Publicar vaga</span>
                                <span class="sm:hidden">Vaga</span>
                            </a>
                        @elseif (auth()->check())
                            @if ($isFollowing)
                                <button wire:click="unfollow"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-white px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-slate-800 ring-1 ring-slate-200 hover:bg-slate-50 whitespace-nowrap dark:bg-slate-800 dark:text-slate-100 dark:ring-slate-700 dark:hover:bg-slate-700">
                                    <x-icon name="check" class="h-4 w-4"/> Seguindo
                                </button>
                            @else
                                <button wire:click="follow"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-500 px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-soft hover:bg-brand-600 whitespace-nowrap">
                                    <x-icon name="plus" class="h-4 w-4"/> Seguir
                                </button>
                            @endif
                            @if ($user)
                                <a href="{{ route('messages.index', ['user' => $profile?->user?->id]) }}"
                                   class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-white px-3 py-2 text-xs sm:text-sm font-semibold text-slate-800 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-100 dark:ring-slate-700 dark:hover:bg-slate-700"
                                   title="Enviar mensagem">
                                    <x-icon name="message" class="h-4 w-4"/>
                                    <span class="hidden sm:inline">Mensagem</span>
                                </a>

                                {{-- Menu de 3 pontinhos: bloquear/desbloquear --}}
                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button type="button" @click="open = !open"
                                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-white px-3 py-2 text-xs sm:text-sm font-semibold text-slate-800 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-100 dark:ring-slate-700 dark:hover:bg-slate-700"
                                            title="Mais opções">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="12" cy="5" r="1.75"/><circle cx="12" cy="12" r="1.75"/><circle cx="12" cy="19" r="1.75"/>
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition x-cloak
                                         class="absolute right-0 top-full z-20 mt-1 w-48 overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                                        @if ($isBlocked)
                                            <button type="button" wire:click="unblockUser" @click="open = false"
                                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10">
                                                <x-icon name="check" class="h-4 w-4"/> Desbloquear
                                            </button>
                                        @else
                                            <button type="button" wire:click="blockUser"
                                                    wire:confirm="Bloquear esta empresa? Vocês não poderão trocar mensagens nem ver posts um do outro."
                                                    @click="open = false"
                                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">
                                                <x-icon name="x" class="h-4 w-4"/> Bloquear empresa
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand-500 px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-soft hover:bg-brand-600 whitespace-nowrap">
                                <x-icon name="plus" class="h-4 w-4"/> Seguir
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Linha 2: Nome / info da empresa (fica ABAIXO da capa, embaixo do logo) --}}
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <h1 class="font-display text-xl sm:text-2xl md:text-3xl font-bold text-slate-900 dark:text-white">
                            {{ $profile?->trade_name ?: $profile?->legal_name ?? 'Empresa' }}
                        </h1>
                        @if ($profile?->domain_verified)
                            <span class="inline-flex items-center gap-1 rounded-full bg-brand-500/10 px-2 py-0.5 text-[10px] sm:text-xs font-semibold text-brand-700 dark:text-brand-300">
                                <x-icon name="check" class="h-3 w-3"/> Verificada
                            </span>
                        @endif
                    </div>

                    @if ($profile?->trade_name && $profile?->legal_name && $profile->trade_name !== $profile->legal_name)
                        <p class="mt-0.5 text-xs sm:text-sm text-slate-500">
                            {{ $profile->legal_name }}
                        </p>
                    @endif

                    {{-- Meta info: setor · porte · localização (com wrap suave) --}}
                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                        @if ($profile?->industry)
                            <span class="inline-flex items-center gap-1">
                                <x-icon name="briefcase" class="h-3.5 w-3.5"/>
                                {{ $profile->industry }}
                            </span>
                        @endif
                        @if ($profile?->size)
                            <span class="inline-flex items-center gap-1">
                                <x-icon name="user" class="h-3.5 w-3.5"/>
                                {{ $profile->size }} pessoas
                            </span>
                        @endif
                        @if ($location)
                            <span class="inline-flex items-center gap-1">
                                <x-icon name="home" class="h-3.5 w-3.5"/>
                                {{ $location }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 STATS BAR — 3 métricas em linha, sempre visíveis
                 ============================================================ --}}
            <div class="mt-5 grid grid-cols-3 gap-2 sm:gap-3">
                <div class="rounded-xl bg-slate-50 px-3 py-2.5 sm:py-3 text-center dark:bg-slate-800/60">
                    <p class="text-base sm:text-xl font-display font-bold text-slate-900 dark:text-white">
                        {{ number_format($followersCount, 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] sm:text-xs font-medium text-slate-500">
                        {{ $followersCount === 1 ? 'seguidor' : 'seguidores' }}
                    </p>
                </div>
                <div class="rounded-xl bg-slate-50 px-3 py-2.5 sm:py-3 text-center dark:bg-slate-800/60">
                    <p class="text-base sm:text-xl font-display font-bold text-brand-600 dark:text-brand-400">
                        {{ $openJobs->count() }}
                    </p>
                    <p class="text-[10px] sm:text-xs font-medium text-slate-500">
                        vagas abertas
                    </p>
                </div>
                <div class="rounded-xl bg-slate-50 px-3 py-2.5 sm:py-3 text-center dark:bg-slate-800/60">
                    <p class="text-base sm:text-xl font-display font-bold text-accent-600 dark:text-accent-400">
                        {{ $postsCount }}
                    </p>
                    <p class="text-[10px] sm:text-xs font-medium text-slate-500">
                        {{ $postsCount === 1 ? 'publicação' : 'publicações' }}
                    </p>
                </div>
            </div>

            {{-- Visualizações do perfil (só o dono da empresa vê) --}}
            @if ($isOwner)
                @php $viewsCount = (int) ($profile?->user?->profile_views_count ?? 0); @endphp
                <div class="mt-3 flex items-center gap-1.5 rounded-xl bg-slate-50 px-3 py-2 text-xs text-slate-600 dark:bg-slate-800/60 dark:text-slate-300">
                    <x-icon name="eye" class="h-4 w-4 text-brand-500"/>
                    <span>
                        <strong class="font-bold text-slate-900 dark:text-white">{{ number_format($viewsCount, 0, ',', '.') }}</strong>
                        {{ $viewsCount === 1 ? 'visualização do seu perfil' : 'visualizações do seu perfil' }}
                    </span>
                </div>
            @endif

            {{-- Barra de avaliações — clicável, leva pra aba reviews --}}
            @if ($reviewsCount > 0)
                <button type="button" wire:click="setTab('reviews')"
                        class="mt-3 flex w-full items-center justify-between gap-3 rounded-xl bg-amber-50 px-3 py-2 text-left transition hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⭐</span>
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">
                                {{ number_format((float) $averageRating, 1, ',', '') }}
                                <span class="text-xs font-normal text-slate-500">/ 5,0</span>
                            </p>
                            <p class="text-[10px] text-slate-500">
                                {{ $reviewsCount }} {{ $reviewsCount === 1 ? 'avaliação' : 'avaliações' }} · {{ $recommendationRate }}% recomendariam
                            </p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-300">Ver avaliações →</span>
                </button>
            @elseif ($canReview)
                <button type="button" wire:click="setTab('reviews')"
                        class="mt-3 flex w-full items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2 text-left hover:bg-slate-100 dark:bg-slate-800/60 dark:hover:bg-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="text-lg opacity-60">⭐</span>
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            Você teve processo com esta empresa. <span class="font-semibold text-brand-600 dark:text-brand-400">Seja o primeiro a avaliar!</span>
                        </p>
                    </div>
                </button>
            @endif

            {{-- Website / contato (barra pequena discreta abaixo) --}}
            @if ($profile?->website || $profile?->phone)
                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-slate-100 pt-3 text-xs sm:text-sm dark:border-slate-800">
                    @if ($profile?->website)
                        <a href="{{ $profile->website }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 text-brand-600 hover:underline dark:text-brand-400">
                            <x-icon name="arrow-right" class="h-3.5 w-3.5"/>
                            {{ preg_replace('~^https?://(www\.)?~i', '', rtrim($profile->website, '/')) }}
                        </a>
                    @endif
                    @if ($profile?->phone)
                        <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                            <x-icon name="message" class="h-3.5 w-3.5"/>
                            {{ $profile->phone }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </section>

    {{-- ============================================================
         ABAS — sticky no topo do conteúdo, scroll horizontal em mobile
         ============================================================ --}}
    <div class="sticky top-16 z-20 -mx-4 sm:-mx-0 border-y border-slate-100 bg-white/95 backdrop-blur px-4 sm:px-0 sm:border-y-0 sm:bg-transparent sm:backdrop-blur-none dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex gap-1 overflow-x-auto scrollbar-none py-2 sm:py-0 sm:rounded-2xl sm:bg-white sm:p-2 sm:shadow-soft sm:ring-1 sm:ring-slate-100 sm:dark:bg-slate-900 sm:dark:ring-slate-800">
            @php
                $tabs = [
                    'about'   => ['Sobre',        null,                'user'],
                    'jobs'    => ['Vagas',        $openJobs->count(),  'briefcase'],
                    'posts'   => ['Publicações',  $postsCount,         'sparkles'],
                    'reviews' => ['Avaliações',   $reviewsCount,       'sparkles'],
                ];
            @endphp
            @foreach ($tabs as $key => [$label, $count, $icon])
                <button wire:click="setTab('{{ $key }}')"
                        class="flex flex-shrink-0 items-center gap-1.5 rounded-xl px-3 sm:px-4 py-2 text-sm font-medium whitespace-nowrap transition
                               {{ $tab === $key
                                    ? 'bg-brand-500 text-white shadow-soft'
                                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    <x-icon :name="$icon" class="h-4 w-4"/>
                    {{ $label }}
                    @if (! is_null($count) && $count > 0)
                        <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold
                                     {{ $tab === $key ? 'bg-white/25 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                            {{ $count }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- ============================================================
         CONTEÚDO DA ABA
         ============================================================ --}}
    <div wire:key="tab-{{ $tab }}">
        @switch($tab)

            {{-- ================================================ SOBRE ================================================ --}}
            @case('about')
                <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">

                    {{-- Descrição --}}
                    <div class="card lg:col-span-2">
                        <div class="flex items-center gap-2">
                            <x-icon name="sparkles" class="h-5 w-5 text-brand-500"/>
                            <h2 class="font-display text-lg font-bold">Sobre a empresa</h2>
                        </div>
                        @if ($profile?->about)
                            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                                {{ $profile->about }}
                            </p>
                        @else
                            <div class="mt-3 rounded-xl bg-slate-50 p-4 text-center dark:bg-slate-800/60">
                                <x-icon name="sparkles" class="mx-auto h-6 w-6 text-slate-400"/>
                                <p class="mt-2 text-sm text-slate-500">
                                    {{ $isOwner ? 'Você ainda não escreveu sobre sua empresa.' : 'Esta empresa ainda não escreveu uma descrição.' }}
                                </p>
                                @if ($isOwner)
                                    <a href="{{ route('company.edit') }}" class="mt-2 inline-block text-xs font-semibold text-brand-600 hover:underline">
                                        Adicionar descrição →
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Card lateral: Ficha rápida --}}
                    <aside class="card">
                        <h3 class="font-display text-sm font-bold text-slate-500 uppercase tracking-wider">Ficha</h3>
                        <dl class="mt-3 space-y-3 text-sm">
                            @if ($profile?->cnpj)
                                <div>
                                    <dt class="text-xs font-medium text-slate-500">CNPJ</dt>
                                    <dd class="font-mono text-slate-800 dark:text-slate-200">
                                        {{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $profile->cnpj) }}
                                    </dd>
                                </div>
                            @endif
                            @if ($profile?->legal_name)
                                <div>
                                    <dt class="text-xs font-medium text-slate-500">Razão social</dt>
                                    <dd class="text-slate-800 dark:text-slate-200">{{ $profile->legal_name }}</dd>
                                </div>
                            @endif
                            @if ($profile?->industry)
                                <div>
                                    <dt class="text-xs font-medium text-slate-500">Setor</dt>
                                    <dd class="text-slate-800 dark:text-slate-200">{{ $profile->industry }}</dd>
                                </div>
                            @endif
                            @if ($profile?->size)
                                <div>
                                    <dt class="text-xs font-medium text-slate-500">Porte</dt>
                                    <dd class="text-slate-800 dark:text-slate-200">{{ $profile->size }} colaboradores</dd>
                                </div>
                            @endif
                            @if ($location)
                                <div>
                                    <dt class="text-xs font-medium text-slate-500">Localização</dt>
                                    <dd class="text-slate-800 dark:text-slate-200">{{ $location }}</dd>
                                </div>
                            @endif
                        </dl>

                        {{-- Vagas em destaque (topo 3) — só se tiver --}}
                        @if ($openJobs->count() > 0)
                            <div class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800">
                                <h4 class="font-display text-sm font-bold text-slate-500 uppercase tracking-wider">
                                    Vagas em destaque
                                </h4>
                                <ul class="mt-3 space-y-2">
                                    @foreach ($openJobs->take(3) as $job)
                                        <li>
                                            <a href="{{ route('jobs.show', $job) }}"
                                               class="block rounded-xl p-2 -mx-2 hover:bg-slate-50 dark:hover:bg-slate-800">
                                                <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">
                                                    {{ $job->title }}
                                                </p>
                                                <p class="mt-0.5 truncate text-xs text-slate-500">
                                                    @if ($job->modality){{ ucfirst($job->modality) }}@endif
                                                    @if ($job->location) · {{ $job->location }}@endif
                                                </p>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                @if ($openJobs->count() > 3)
                                    <button wire:click="setTab('jobs')"
                                            class="mt-2 text-xs font-semibold text-brand-600 hover:underline">
                                        Ver todas ({{ $openJobs->count() }}) →
                                    </button>
                                @endif
                            </div>
                        @endif
                    </aside>
                </div>
                @break

            {{-- ================================================ VAGAS ================================================ --}}
            @case('jobs')
                <div class="card">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-icon name="briefcase" class="h-5 w-5 text-brand-500"/>
                            <h2 class="font-display text-lg font-bold">
                                Vagas ativas
                                <span class="ml-1 text-sm font-normal text-slate-500">({{ $openJobs->count() }})</span>
                            </h2>
                        </div>
                        @if ($isOwner)
                            <a href="{{ route('jobs.create') }}"
                               class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600">
                                <x-icon name="plus" class="h-3.5 w-3.5"/> Nova
                            </a>
                        @endif
                    </div>

                    @if ($openJobs->count())
                        <ul class="mt-4 space-y-3">
                            @foreach ($openJobs as $job)
                                <li>
                                    <a href="{{ route('jobs.show', $job) }}"
                                       class="group flex items-start justify-between gap-3 rounded-2xl border border-slate-200 p-3 sm:p-4 transition hover:border-brand-500 hover:shadow-soft dark:border-slate-700 dark:hover:border-brand-500">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate font-semibold text-slate-900 group-hover:text-brand-600 dark:text-white">
                                                {{ $job->title }}
                                            </p>
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                @if ($job->modality)
                                                    <x-chip color="brand">{{ ucfirst($job->modality) }}</x-chip>
                                                @endif
                                                @if ($job->seniority)
                                                    <x-chip color="accent">{{ ucfirst($job->seniority) }}</x-chip>
                                                @endif
                                                @if ($job->contract_type)
                                                    <x-chip>{{ $job->contract_type }}</x-chip>
                                                @endif
                                                @if ($job->location)
                                                    <x-chip>{{ $job->location }}</x-chip>
                                                @endif
                                            </div>
                                            @if ($job->published_at)
                                                <p class="mt-2 text-xs text-slate-500">
                                                    Publicada {{ $job->published_at->diffForHumans() }}
                                                </p>
                                            @endif
                                        </div>
                                        <x-icon name="arrow-right" class="mt-1 h-5 w-5 shrink-0 text-slate-400 transition group-hover:translate-x-1 group-hover:text-brand-500"/>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="mt-4 rounded-xl bg-slate-50 p-8 text-center dark:bg-slate-800/60">
                            <x-icon name="briefcase" class="mx-auto h-8 w-8 text-slate-400"/>
                            <p class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                                Nenhuma vaga ativa no momento.
                            </p>
                            @if ($isOwner)
                                <a href="{{ route('jobs.create') }}"
                                   class="mt-3 inline-flex items-center gap-1 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">
                                    <x-icon name="plus" class="h-4 w-4"/> Publicar primeira vaga
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
                @break

            {{-- ================================================ PUBLICAÇÕES ================================================ --}}
            @case('posts')
                <div class="mb-4 flex items-center gap-2">
                    <div class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-400">
                        <x-icon name="chat" class="h-4 w-4"/>
                    </div>
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white sm:text-xl">
                        Publicações
                        <span class="ml-1 text-sm font-normal text-slate-500">({{ $postsCount }})</span>
                    </h2>
                </div>

                @if ($posts->count())
                    <div class="space-y-4">
                        @foreach ($posts as $post)
                            <livewire:feed.post-card :post-id="$post->id"
                                                     :wire:key="'company-post-' . $post->id"/>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center dark:border-slate-700 dark:bg-slate-800/50">
                        <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                            <x-icon name="chat" class="h-6 w-6"/>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-300">
                            {{ $isOwner ? 'Você ainda não publicou nada.' : 'A empresa ainda não publicou nada.' }}
                        </p>
                        @if ($isOwner)
                            <a href="{{ route('feed') }}" wire:navigate class="btn-primary mt-4 inline-flex text-xs">
                                <x-icon name="plus" class="h-4 w-4"/> Fazer primeira publicação
                            </a>
                        @endif
                    </div>
                @endif
                @break

            {{-- ================================================ AVALIAÇÕES ================================================ --}}
            @case('reviews')
                <div class="space-y-4">

                    {{-- Card resumo --}}
                    <div class="card">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-amber-100 text-2xl dark:bg-amber-500/20">
                                    ⭐
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Nota geral</p>
                                    <p class="font-display text-2xl font-bold text-slate-900 dark:text-white">
                                        {{ number_format((float) $averageRating, 1, ',', '') }}
                                        <span class="text-sm font-normal text-slate-500">/ 5,0</span>
                                    </p>
                                    <div class="mt-0.5 flex items-center gap-0.5 text-sm">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span class="{{ round($averageRating) >= $i ? '' : 'grayscale opacity-30' }}">⭐</span>
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="font-display text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ $recommendationRate }}%
                                </p>
                                <p class="text-xs text-slate-500">
                                    recomendariam ({{ $reviewsCount }} {{ $reviewsCount === 1 ? 'avaliação' : 'avaliações' }})
                                </p>
                            </div>
                        </div>

                        @if ($canReview)
                            <a href="{{ route('reviews.create', ['company' => $profile->slug]) }}"
                               class="btn-primary mt-4 inline-flex items-center gap-1.5">
                                <x-icon name="sparkles" class="h-4 w-4"/>
                                Escrever minha avaliação
                            </a>
                        @elseif ($hasReviewed)
                            <p class="mt-4 rounded-xl bg-brand-50 px-3 py-2 text-xs text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                ✓ Você já avaliou esta empresa.
                            </p>
                        @endif
                    </div>

                    {{-- Lista de reviews --}}
                    @if ($reviews->count())
                        <ul class="space-y-3">
                            @foreach ($reviews as $review)
                                <li class="card">
                                    {{-- Cabeçalho: autor + data --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            @if ($review->is_anonymous)
                                                <div class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800">
                                                    <x-icon name="user" class="h-5 w-5"/>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-200">
                                                        Anônimo
                                                    </p>
                                                    <p class="text-xs text-slate-500">
                                                        {{ optional($review->created_at)->diffForHumans() }}
                                                    </p>
                                                </div>
                                            @else
                                                @if ($review->user?->avatar_path)
                                                    <img src="{{ $review->user->avatar_url }}"
                                                         alt="{{ $review->user->name }}"
                                                         class="h-10 w-10 rounded-full object-cover">
                                                @else
                                                    <div class="grid h-10 w-10 place-items-center rounded-full bg-brand-100 text-sm font-bold text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                                                        {{ mb_substr($review->user?->name ?? '?', 0, 1) }}
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-200">
                                                        {{ $review->user?->name ?? 'Usuário' }}
                                                    </p>
                                                    <p class="text-xs text-slate-500">
                                                        {{ optional($review->created_at)->diffForHumans() }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>

                                        @if ($review->would_recommend)
                                            <span class="inline-flex flex-shrink-0 items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:text-emerald-300">
                                                <x-icon name="check" class="h-3 w-3"/> Recomenda
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Estrelas + Título --}}
                                    <div class="mt-3">
                                        <div class="flex items-center gap-0.5 text-sm">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <span class="{{ $review->rating_overall >= $i ? '' : 'grayscale opacity-30' }}">⭐</span>
                                            @endfor
                                            <span class="ml-1 text-xs font-semibold text-slate-500">
                                                {{ $review->rating_overall }}/5
                                            </span>
                                        </div>
                                        <h3 class="mt-1 font-display text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                                            {{ $review->title }}
                                        </h3>
                                    </div>

                                    {{-- Notas por dimensão --}}
                                    <div class="mt-3 grid grid-cols-3 gap-2 text-center text-[11px]">
                                        <div class="rounded-lg bg-slate-50 px-2 py-1.5 dark:bg-slate-800/60">
                                            <p class="text-slate-500">Processo</p>
                                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $review->rating_process }}/5</p>
                                        </div>
                                        <div class="rounded-lg bg-slate-50 px-2 py-1.5 dark:bg-slate-800/60">
                                            <p class="text-slate-500">Comunicação</p>
                                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $review->rating_communication }}/5</p>
                                        </div>
                                        <div class="rounded-lg bg-slate-50 px-2 py-1.5 dark:bg-slate-800/60">
                                            <p class="text-slate-500">Cultura</p>
                                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $review->rating_culture }}/5</p>
                                        </div>
                                    </div>

                                    {{-- Pros / Cons --}}
                                    <div class="mt-3 space-y-2 text-sm">
                                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-3 dark:border-emerald-500/20 dark:bg-emerald-500/5">
                                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">
                                                O que gostou
                                            </p>
                                            <p class="mt-1 whitespace-pre-line text-slate-800 dark:text-slate-200">{{ $review->pros }}</p>
                                        </div>
                                        <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-3 dark:border-rose-500/20 dark:bg-rose-500/5">
                                            <p class="text-xs font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-300">
                                                O que poderia melhorar
                                            </p>
                                            <p class="mt-1 whitespace-pre-line text-slate-800 dark:text-slate-200">{{ $review->cons }}</p>
                                        </div>
                                    </div>

                                    {{-- Resposta da empresa (existente) --}}
                                    @if ($review->company_response)
                                        <div class="mt-3 rounded-xl bg-brand-50 p-3 dark:bg-brand-500/10">
                                            <div class="flex items-center gap-2">
                                                <x-icon name="sparkles" class="h-4 w-4 text-brand-600 dark:text-brand-300"/>
                                                <p class="text-xs font-semibold text-brand-700 dark:text-brand-300">
                                                    Resposta da empresa
                                                    @if ($review->company_responded_at)
                                                        <span class="font-normal text-brand-600/70 dark:text-brand-300/70">
                                                            · {{ $review->company_responded_at->diffForHumans() }}
                                                        </span>
                                                    @endif
                                                </p>
                                            </div>
                                            <p class="mt-1 whitespace-pre-line text-sm text-slate-800 dark:text-slate-100">
                                                {{ $review->company_response }}
                                            </p>
                                        </div>
                                    @elseif ($isOwner)
                                        {{-- Empresa dona pode responder --}}
                                        @if ($respondingToReviewId === $review->id)
                                            <div class="mt-3 rounded-xl border border-brand-200 bg-white p-3 dark:border-brand-500/30 dark:bg-slate-900">
                                                <p class="text-xs font-semibold text-brand-700 dark:text-brand-300">
                                                    Respondendo publicamente
                                                </p>
                                                <textarea wire:model.defer="responseText" rows="3" class="input mt-2"
                                                          placeholder="Escreva uma resposta pública..."></textarea>
                                                @error('responseText') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                                <div class="mt-2 flex justify-end gap-2">
                                                    <button type="button" wire:click="cancelResponse"
                                                            class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700">
                                                        Cancelar
                                                    </button>
                                                    <button type="button" wire:click="respondToReview({{ $review->id }})"
                                                            class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600">
                                                        Publicar resposta
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <button type="button" wire:click="startResponse({{ $review->id }})"
                                                    class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:bg-brand-500/20">
                                                <x-icon name="message" class="h-3.5 w-3.5"/>
                                                Responder
                                            </button>
                                        @endif
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="card">
                            <div class="rounded-xl bg-slate-50 p-8 text-center dark:bg-slate-800/60">
                                <x-icon name="sparkles" class="mx-auto h-8 w-8 text-slate-400"/>
                                <p class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Ainda não há avaliações. Seja o primeiro a avaliar!
                                </p>
                                @if ($canReview)
                                    <a href="{{ route('reviews.create', ['company' => $profile->slug]) }}"
                                       class="mt-3 inline-flex items-center gap-1 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">
                                        <x-icon name="sparkles" class="h-4 w-4"/>
                                        Escrever avaliação
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                @break
        @endswitch
    </div>
</div>
