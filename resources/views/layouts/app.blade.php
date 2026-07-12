<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'SocialJobs') }}</title>

    <link rel="icon" type="image/x-icon" href="/favicon/favicon.ico">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="/favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-title" content="SocialJobs">
    <link rel="manifest" href="/favicon/site.webmanifest">
    <meta name="theme-color" content="#10b981">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')

    <script>
        // Aplica tema o quanto antes para evitar "flash"
        (function () {
            var stored = localStorage.getItem('SocialJobs-theme');
            if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="min-h-screen overflow-x-hidden bg-paper text-ink antialiased dark:bg-ink-dark dark:text-slate-100"
      x-data="{
          theme: localStorage.getItem('SocialJobs-theme') || 'light',
          drawer: false,
          toggle() {
              this.theme = this.theme === 'dark' ? 'light' : 'dark';
              localStorage.setItem('SocialJobs-theme', this.theme);
              document.documentElement.classList.toggle('dark', this.theme === 'dark');
          }
      }"
      x-effect="drawer ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden')">

    {{-- HEADER --}}
    <header class="sticky top-0 z-40 border-b border-slate-100 bg-white/80 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80">
        <div class="mx-auto flex max-w-7xl items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3">
            {{-- Botão hambúrguer:
                 - Sempre visível no mobile (< lg)
                 - No desktop (>= lg): só aparece nas páginas que NÃO são o feed
                   (o feed mantém sidebar esquerda com o menu tradicional). --}}
            @auth
                @php
                    $isFeedPage = request()->routeIs('feed', 'posts.*');
                @endphp
                <button type="button"
                        @click="drawer = true"
                        class="grid h-9 w-9 place-items-center rounded-xl text-slate-600 hover:bg-slate-100 hover:text-ink dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white flex-shrink-0
                               {{ $isFeedPage ? 'lg:hidden' : '' }}"
                        aria-label="Abrir menu">
                    <x-icon name="menu" class="h-5 w-5"/>
                </button>
            @endauth

            {{-- Logo --}}
            <a href="{{ auth()->check() ? route('feed') : route('landing') }}" class="flex items-center gap-2 min-w-0 flex-shrink-0">
                <img src="/favicon/favicon-96x96.png"
                     alt="SocialJobs"
                     width="36" height="36"
                     class="h-8 w-8 sm:h-9 sm:w-9 rounded-2xl shadow-soft flex-shrink-0">
                <span class="font-display text-base sm:text-lg md:text-xl font-bold tracking-tight truncate">SocialJobs</span>
            </a>

            {{-- Busca global (desktop) — componente Livewire com autocomplete --}}
            <div class="hidden flex-1 justify-center md:flex min-w-0">
                <livewire:search.bar/>
            </div>

            <div class="ml-auto flex items-center gap-1 sm:gap-2 flex-shrink-0">
                {{-- Theme toggle --}}
                <button type="button" @click="toggle()"
                        class="grid h-9 w-9 place-items-center rounded-xl text-slate-600 hover:bg-slate-100 hover:text-ink dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white flex-shrink-0"
                        title="Alternar tema"
                        aria-label="Alternar tema">
                    <template x-if="theme === 'dark'"><x-icon name="sun" class="h-5 w-5"/></template>
                    <template x-if="theme !== 'dark'"><x-icon name="moon" class="h-5 w-5"/></template>
                </button>

                {{-- Notificações --}}
                @auth
                    <livewire:notifications.bell/>
                @endauth

                @auth
                    {{-- Avatar dropdown --}}
                    @php
                        // Nome de exibição no dropdown/header:
                        // - Empresas mostram nome fantasia/razão social (não o nome
                        //   do responsável de contato)
                        // - Candidatos mostram o nome normal
                        $__hdrIsCompany = (auth()->user()->type ?? 'candidate') === 'company';
                        $__hdrDisplayName = auth()->user()->name;
                        if ($__hdrIsCompany && ($cp = auth()->user()->companyProfile)) {
                            $__hdrDisplayName = $cp->trade_name ?: ($cp->legal_name ?: $__hdrDisplayName);
                        }
                    @endphp
                    <div x-data="{ open: false }" class="relative flex-shrink-0">
                        <button @click="open = !open" class="flex items-center gap-2 rounded-full ring-1 ring-slate-200 p-0.5 hover:ring-brand-500 dark:ring-slate-700 sm:p-1 sm:pr-3">
                            <x-avatar :user="auth()->user()" size="sm"/>
                            <span class="hidden text-sm font-medium sm:block truncate max-w-[140px]">{{ $__hdrDisplayName }}</span>
                            <x-icon name="chevron-down" class="hidden h-4 w-4 text-slate-400 sm:block flex-shrink-0"/>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition
                             class="absolute right-0 mt-2 w-56 origin-top-right rounded-2xl bg-white p-1 shadow-soft-lg ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700"
                             x-cloak>
                            @php
                                // URL do perfil depende do tipo:
                                // - candidato → /u/{username}
                                // - empresa   → /c/{slug do CompanyProfile}
                                $u = auth()->user();
                                if (($u->type ?? 'candidate') === 'company') {
                                    $slug = optional($u->companyProfile)->slug;
                                    $profileUrl = $slug ? url('/c/' . $slug) : url('/u/' . ($u->username ?? $u->id));
                                } else {
                                    $profileUrl = url('/u/' . ($u->username ?? $u->id));
                                }
                            @endphp
                            <a href="{{ $profileUrl }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-800">
                                <x-icon name="user" class="h-4 w-4"/> Meu perfil
                            </a>
                            <a href="{{ route('settings', 'account') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-800">
                                <x-icon name="cog" class="h-4 w-4"/> Configurações
                            </a>
                            @if (! $__hdrIsCompany)
                                <a href="{{ route('points') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-800">
                                    <x-icon name="trophy" class="h-4 w-4"/> Pontos e nível
                                </a>
                            @endif
                            <hr class="my-1 border-slate-100 dark:border-slate-800">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">
                                    <x-icon name="logout" class="h-4 w-4"/> Sair
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    {{-- Botão único que abre dropdown com Entrar / Cadastrar-se.
                         Assim o usuário escolhe qual fluxo quer sem ocupar espaço no header. --}}
                    <div x-data="{ open: false }" class="relative flex-shrink-0">
                        <button type="button" @click="open = !open"
                                class="inline-flex items-center justify-center gap-1 rounded-xl bg-brand-500 px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-soft hover:bg-brand-600 whitespace-nowrap"
                                aria-haspopup="menu"
                                :aria-expanded="open">
                            Entrar / Cadastrar-se
                            <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }"
                                 viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.3 7.3a1 1 0 011.4 0L10 10.6l3.3-3.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 010-1.4z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition
                             class="absolute right-0 mt-2 w-52 origin-top-right rounded-2xl bg-white p-1 shadow-soft-lg ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700"
                             role="menu"
                             x-cloak>
                            <a href="{{ route('login') }}"
                               class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">
                                <x-icon name="user" class="h-4 w-4 text-brand-500"/>
                                <span>Já tenho conta — <b>Entrar</b></span>
                            </a>
                            <a href="{{ route('register') }}"
                               class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-brand-50 hover:text-brand-700 dark:text-slate-200 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                                <x-icon name="sparkles" class="h-4 w-4 text-accent-500"/>
                                <span>Sou novo — <b>Cadastrar-se</b></span>
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    {{-- ============================================================
         DRAWER MOBILE — menu completo aberto pelo hambúrguer
         ============================================================ --}}
    @auth
        @php
            // Menu adaptado ao tipo de usuário: empresas veem "Publicar vaga"
            // e "Kanban" no lugar de "Testes" e "Minhas candidaturas".
            $isCompany = (auth()->user()->type ?? 'candidate') === 'company';

            // Nome de exibição: para empresas usamos o nome fantasia/razão social
            // do CompanyProfile em vez do nome do responsável de contato.
            $authDisplayName = auth()->user()->name;
            if ($isCompany) {
                $cp = auth()->user()->companyProfile;
                if ($cp) {
                    $authDisplayName = $cp->trade_name ?: ($cp->legal_name ?: $authDisplayName);
                }
            }

            $drawerNav = $isCompany
                ? [
                    ['route' => 'feed',                     'label' => 'Feed',        'icon' => 'home'],
                    ['route' => 'company.dashboard',        'label' => 'Dashboard',   'icon' => 'chart-bar'],
                    ['route' => 'jobs.index',               'label' => 'Ver vagas',   'icon' => 'briefcase'],
                    ['route' => 'jobs.create',              'label' => 'Publicar vaga','icon' => 'plus'],
                    ['route' => 'company.kanban',           'label' => 'Kanban de candidaturas', 'icon' => 'target'],
                    ['route' => 'company.talents',          'label' => 'Buscar candidatos', 'icon' => 'search'],
                    ['route' => 'company.ai',               'label' => 'Assistente IA (empresa)', 'icon' => 'robot'],
                    ['route' => 'company.assessments.index','label' => 'Testes',      'icon' => 'clipboard'],
                    ['route' => 'company.courses.index',    'label' => 'Cursos internos', 'icon' => 'academic'],
                    ['route' => 'messages.index',           'label' => 'Mensagens',   'icon' => 'message'],
                    ['route' => 'settings',                 'label' => 'Configurações','icon' => 'cog'],
                ]
                : [
                    ['route' => 'feed',                     'label' => 'Feed',        'icon' => 'home'],
                    ['route' => 'jobs.index',               'label' => 'Vagas',       'icon' => 'briefcase'],
                    ['route' => 'skill-assessments.index',  'label' => 'Testes',      'icon' => 'clipboard'],
                    ['route' => 'ai',                       'label' => 'Assistente IA', 'icon' => 'robot'],
                    ['route' => 'messages.index',   'label' => 'Mensagens',   'icon' => 'message'],
                    ['route' => 'points',           'label' => 'Pontos',      'icon' => 'bolt'],
                    ['route' => 'leaderboard',      'label' => 'Ranking',     'icon' => 'trophy'],
                    ['route' => 'applications.mine','label' => 'Minhas candidaturas', 'icon' => 'check'],
                    ['route' => 'settings',         'label' => 'Configurações','icon' => 'cog'],
                ];
        @endphp

        {{-- Overlay --}}
        <div x-show="drawer" x-transition.opacity
             @click="drawer = false"
             class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm"
             x-cloak></div>

        {{-- Painel lateral --}}
        <aside x-show="drawer"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="fixed left-0 top-0 z-50 flex h-full w-80 max-w-[85vw] flex-col bg-white shadow-2xl dark:bg-slate-900"
               x-cloak>
            {{-- Cabeçalho do drawer --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <img src="/favicon/favicon-96x96.png"
                         alt="SocialJobs"
                         width="36" height="36"
                         class="h-9 w-9 rounded-2xl">
                    <span class="font-display text-lg font-bold">SocialJobs</span>
                </div>
                <button @click="drawer = false"
                        class="grid h-9 w-9 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                        aria-label="Fechar menu">
                    <x-icon name="x" class="h-5 w-5"/>
                </button>
            </div>

            {{-- Cartão do usuário — mostra link para /c/{slug} se empresa, /u/{username} se candidato --}}
            @php
                $du = auth()->user();
                if (($du->type ?? 'candidate') === 'company') {
                    $duSlug = optional($du->companyProfile)->slug;
                    $drawerProfileUrl = $duSlug ? url('/c/' . $duSlug) : url('/u/' . ($du->username ?? $du->id));
                } else {
                    $drawerProfileUrl = url('/u/' . ($du->username ?? $du->id));
                }
            @endphp
            <a href="{{ $drawerProfileUrl }}"
               @click="drawer = false"
               class="mx-3 mt-3 flex items-center gap-3 rounded-2xl bg-slate-50 p-3 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/70">
                <x-avatar :user="auth()->user()" size="md"/>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold">{{ $authDisplayName }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $isCompany ? 'Ver perfil da empresa' : 'Ver meu perfil' }}</p>
                </div>
                <x-icon name="arrow-right" class="h-4 w-4 text-slate-400"/>
            </a>

            {{-- Busca dentro do drawer: link para a página de busca (navegação simples).
                 O autocomplete real só faz sentido no header desktop. --}}
            <a href="{{ route('search') }}"
               @click="drawer = false"
               class="mx-3 mt-3 flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700">
                <x-icon name="search" class="h-4 w-4"/>
                Buscar na plataforma
            </a>

            {{-- Navegação --}}
            <nav class="flex-1 overflow-y-auto px-2 pb-4">
                <ul class="space-y-1">
                    @foreach ($drawerNav as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <li>
                            <a href="{{ route($item['route']) }}"
                               @click="drawer = false"
                               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                                      {{ $active
                                          ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300'
                                          : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800' }}">
                                <x-icon :name="$item['icon']" class="h-5 w-5"/>
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- Footer institucional (links legais + copyright) --}}
            <div class="border-t border-slate-100 px-4 py-3 text-[11px] leading-relaxed text-slate-500 dark:border-slate-800 dark:text-slate-400">
                <div class="flex flex-wrap gap-x-3 gap-y-1">
                    <a href="{{ url('/legal/terms') }}" class="hover:text-brand-600 hover:underline dark:hover:text-brand-400">Termos de uso</a>
                    <span aria-hidden="true">·</span>
                    <a href="{{ url('/legal/privacy') }}" class="hover:text-brand-600 hover:underline dark:hover:text-brand-400">Privacidade</a>
                    <span aria-hidden="true">·</span>
                    <a href="{{ url('/legal/cookies') }}" class="hover:text-brand-600 hover:underline dark:hover:text-brand-400">Cookies</a>
                    <span aria-hidden="true">·</span>
                    <a href="mailto:contato@SocialJobs.com.br" class="hover:text-brand-600 hover:underline dark:hover:text-brand-400">Contato</a>
                </div>
                <p class="mt-2 text-[10px] text-slate-400">
                    © {{ date('Y') }} SocialJobs — Feito com carinho no Brasil.
                </p>
            </div>

            {{-- Rodapé com logout --}}
            <form method="POST" action="{{ route('logout') }}"
                  class="border-t border-slate-100 p-2 dark:border-slate-800">
                @csrf
                <button type="submit"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">
                    <x-icon name="logout" class="h-5 w-5"/> Sair
                </button>
            </form>
        </aside>
    @endauth

    @php
        // Decide o layout de cada página autenticada.
        // - Feed (/feed, /posts/*): 3 colunas → sidebar esquerda com menu + conteúdo + sidebar direita
        // - Outras páginas: hambúrguer no topo + conteúdo largo (9 col) + sidebar direita contextual
        // - Páginas focadas (mensagens, IA, etc): conteúdo em largura total, sem sidebar

        $sidebarComponent = null;   // sidebar direita
        $showLeftMenu     = false;  // se o menu vertical da esquerda deve aparecer (só no feed)

        if (auth()->check()) {
            $__layoutIsCompany = (auth()->user()->type ?? '') === 'company';

            if ($__layoutIsCompany
                && request()->routeIs('feed', 'posts.*', 'jobs.*', 'applications.*', 'company.kanban', 'profile.candidate', 'profile.company', 'profile.edit')) {
                // Empresas veem o painel da empresa nas rotas principais em vez das
                // sidebars pensadas para candidatos.
                if (request()->routeIs('feed', 'posts.*')) {
                    $showLeftMenu = true;
                }
                $sidebarComponent = 'sidebar.company-dashboard';
            } elseif (request()->routeIs('feed', 'posts.*')) {
                $showLeftMenu     = true;
                $sidebarComponent = 'sidebar.suggestions';
            } elseif (request()->routeIs('jobs.*', 'applications.*', 'company.kanban')) {
                $sidebarComponent = 'sidebar.recommended-jobs';
            } elseif (request()->routeIs('courses.*')) {
                $sidebarComponent = 'sidebar.recommended-courses';
            } elseif (request()->routeIs('points', 'leaderboard')) {
                $sidebarComponent = 'sidebar.top-community';
            } elseif (request()->routeIs('profile.candidate', 'profile.company', 'profile.edit')) {
                $sidebarComponent = 'sidebar.suggestions';
            }
            // messages*, settings*, ai, interviews*, onboarding => sem sidebar (conteúdo full-width)
        }

        // Largura do conteúdo principal (desktop):
        // - Feed (com 2 sidebars): 6/12
        // - Página com apenas sidebar direita: 9/12
        // - Página sem sidebar: 12/12
        if ($showLeftMenu && $sidebarComponent) {
            $mainCols     = 'lg:col-span-6';
            $containerMax = 'max-w-7xl';
        } elseif ($sidebarComponent) {
            $mainCols     = 'lg:col-span-9';
            $containerMax = 'max-w-6xl';
        } else {
            $mainCols     = 'lg:col-span-12';
            $containerMax = 'max-w-5xl';
        }

        // No feed em mobile, queremos que os cards de post cheguem quase
        // encostados nas laterais — mais espaço de leitura estilo Instagram/X.
        $isFeedPage = request()->routeIs('feed', 'posts.*');
        $mobilePadding = $isFeedPage ? 'px-0 sm:px-4' : 'px-3 sm:px-4';
    @endphp

    <div class="mx-auto grid {{ $containerMax }} grid-cols-12 gap-4 sm:gap-6 {{ $mobilePadding }} py-4 sm:py-6 {{ auth()->check() ? 'pb-24 lg:pb-6' : '' }}">
        {{-- Sidebar esquerda: menu vertical, apenas no Feed no desktop --}}
        @if ($showLeftMenu)
            <aside class="col-span-12 hidden lg:col-span-3 lg:block">
                <div class="sticky top-24 space-y-3">
                    <nav class="card !p-3" aria-label="Menu principal">
                    {{-- Reusa exatamente as mesmas opções do drawer (menu hamburguer),
                         mantendo paridade entre as duas navegações. --}}
                    <ul class="space-y-1">
                        @foreach ($drawerNav as $item)
                            @php $active = request()->routeIs($item['route']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition
                                          {{ $active
                                              ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300'
                                              : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800' }}">
                                    <x-icon :name="$item['icon']" class="h-5 w-5"/>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                {{-- Footer institucional (links legais + copyright) --}}
                <div class="px-3 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                    <div class="flex flex-wrap gap-x-3 gap-y-1">
                        <a href="{{ url('/legal/terms') }}" class="hover:text-brand-600 hover:underline dark:hover:text-brand-400">Termos de uso</a>
                        <span aria-hidden="true">·</span>
                        <a href="{{ url('/legal/privacy') }}" class="hover:text-brand-600 hover:underline dark:hover:text-brand-400">Privacidade</a>
                        <span aria-hidden="true">·</span>
                        <a href="{{ url('/legal/cookies') }}" class="hover:text-brand-600 hover:underline dark:hover:text-brand-400">Cookies</a>
                        <span aria-hidden="true">·</span>
                        <a href="mailto:contato@SocialJobs.com.br" class="hover:text-brand-600 hover:underline dark:hover:text-brand-400">Contato</a>
                    </div>
                    <p class="mt-2 text-[10px] text-slate-400">
                        © {{ date('Y') }} SocialJobs — Feito com carinho no Brasil.
                    </p>
                </div>
                </div>
            </aside>
        @endif

        {{-- Conteúdo principal --}}
        <main class="col-span-12 {{ $mainCols }}">
            @isset($header)
                <div class="mb-4">{{ $header }}</div>
            @endisset

            @if (session('status'))
                <div class="mb-4 rounded-2xl bg-brand-50 p-4 text-sm text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot ?? '' }}
            {{-- Suporte a views que usam @extends('layouts.app') + @section('content') --}}
            @if (trim($__env->yieldContent('content')) !== '')
                @yield('content')
            @endif
        </main>

        {{-- Sidebar direita contextual (só no desktop e apenas quando aplicável) --}}
        @if ($sidebarComponent)
            <aside class="col-span-12 hidden lg:col-span-3 lg:block">
                <div class="sticky top-24 space-y-4">
                    @livewire($sidebarComponent)
                </div>
            </aside>
        @endif
    </div>

    {{-- Footer removido: agora vive dentro do menu (sidebar esquerda no feed / drawer nas demais páginas) --}}

    {{-- ============================================================
         BOTTOM BAR MOBILE — apenas 4 ícones essenciais, sem texto.
         Fica fixa no rodapé para navegação rápida (estilo Instagram).
         ============================================================ --}}
    @auth
        @php
            // Bottom bar mobile também é adaptada ao tipo de usuário
            $bottomNav = (auth()->user()->type ?? 'candidate') === 'company'
                ? [
                    ['route' => 'feed',           'label' => 'Feed',      'icon' => 'home'],
                    ['route' => 'jobs.create',    'label' => 'Nova vaga', 'icon' => 'plus'],
                    ['route' => 'company.kanban', 'label' => 'Kanban',    'icon' => 'target'],
                    ['route' => 'messages.index', 'label' => 'Mensagens', 'icon' => 'message'],
                ]
                : [
                    ['route' => 'feed',           'label' => 'Feed',      'icon' => 'home'],
                    ['route' => 'jobs.index',     'label' => 'Vagas',     'icon' => 'briefcase'],
                    ['route' => 'skill-assessments.index', 'label' => 'Testes', 'icon' => 'clipboard'],
                    ['route' => 'messages.index', 'label' => 'Mensagens', 'icon' => 'message'],
                ];
        @endphp
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-100 bg-white/95 backdrop-blur lg:hidden dark:border-slate-800 dark:bg-slate-900/95"
             style="padding-bottom: env(safe-area-inset-bottom)"
             aria-label="Navegação inferior">
            <ul class="mx-auto flex max-w-md items-stretch justify-around px-2 py-1">
                @foreach ($bottomNav as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <li class="flex-1">
                        <a href="{{ route($item['route']) }}"
                           class="grid h-12 place-items-center rounded-xl transition
                                  {{ $active
                                      ? 'text-brand-600 dark:text-brand-400'
                                      : 'text-slate-400 hover:text-ink dark:hover:text-white' }}"
                           aria-label="{{ $item['label'] }}"
                           aria-current="{{ $active ? 'page' : 'false' }}"
                           title="{{ $item['label'] }}">
                            <div class="relative">
                                <x-icon :name="$item['icon']" class="h-6 w-6" :stroke="$active ? 2 : 1.75"/>
                                @if ($active)
                                    <span class="absolute -bottom-1 left-1/2 h-1 w-1 -translate-x-1/2 rounded-full bg-brand-500"></span>
                                @endif
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        {{-- Chat flutuante estilo Facebook (canto inferior direito, desktop apenas) --}}
        @include('partials.chat-dock')
    @endauth

    @livewireScripts
    @stack('scripts')

</body>
</html>
