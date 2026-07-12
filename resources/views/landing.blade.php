{{--
    Landing Page do SocialJobs
    Filosofia: 100% grátis, para sempre, para todos (candidatos e empresas).
    Nada de "planos premium", nada de "cartão de crédito". Somos gente ajudando gente.

    Design mobile-first: os valores base são pensados pra 320-400px.
    Breakpoints usados:
      sm:  640px (celular grande)
      md:  768px (tablet)
      lg:  1024px (desktop)
--}}
@php
    /* SEO metadata desta página. Todas as strings ficam abaixo para facilitar
       revisão editorial e futuras traduções. */
    $seoTitle       = 'SocialJobs — Empregos, cursos e IA de carreira. 100% grátis.';
    $seoDescription = 'Plataforma social de empregos no Brasil: encontre vagas, faça cursos com certificado, tire testes, converse com empresas e otimize seu currículo com IA. Grátis para candidatos E empresas — hoje e sempre.';
    $seoKeywords    = 'vagas de emprego, empregos gratis, carreira, curriculo online, curriculo gratis, IA para curriculo, cursos gratuitos com certificado, plataforma de empregos brasil, vagas home office, primeiro emprego, contratação, recrutamento, vagas CLT, PJ, estágio';
    $seoUrl         = url('/');
    $seoImage       = url('/favicon/web-app-manifest-512x512.png');
    $orgLogo        = url('/favicon/favicon.svg');
@endphp

@push('head')
    {{-- Descrição, robôs e canonical --}}
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <meta name="author" content="SocialJobs">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="{{ $seoUrl }}">

    {{-- Open Graph (Facebook, LinkedIn, WhatsApp) --}}
    <meta property="og:site_name" content="SocialJobs">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoUrl }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:image:alt" content="Logo do SocialJobs">

    {{-- Twitter/X Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">

    {{-- Schema.org: Organization + WebSite --}}
    <script type="application/ld+json">
    @php
        $ld = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'        => 'Organization',
                    '@id'          => url('/') . '#organization',
                    'name'         => 'SocialJobs',
                    'url'          => url('/'),
                    'logo'         => $orgLogo,
                    'sameAs'       => [],
                    'contactPoint' => [
                        '@type'             => 'ContactPoint',
                        'email'             => 'contato@SocialJobs.com.br',
                        'contactType'       => 'customer support',
                        'areaServed'        => 'BR',
                        'availableLanguage' => ['Portuguese'],
                    ],
                ],
                [
                    '@type'       => 'WebSite',
                    '@id'         => url('/') . '#website',
                    'url'         => url('/'),
                    'name'        => 'SocialJobs',
                    'description' => $seoDescription,
                    'inLanguage'  => 'pt-BR',
                    'publisher'   => ['@id' => url('/') . '#organization'],
                    'potentialAction' => [
                        '@type'       => 'SearchAction',
                        'target'      => url('/jobs') . '?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ];
    @endphp
    {!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

<x-app-layout :title="$seoTitle">
    {{-- ==========================================
         HERO — impactante, com selo de gratuidade
         ========================================== --}}
    <section class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-br from-white via-brand-50/40 to-white py-10 sm:py-16 md:py-24 shadow-soft-lg ring-1 ring-slate-100 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950 dark:ring-slate-800">
        {{-- Blobs de fundo decorativos --}}
        <div class="pointer-events-none absolute -top-24 -left-24 h-48 w-48 sm:h-72 sm:w-72 rounded-full bg-brand-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-48 w-48 sm:h-72 sm:w-72 rounded-full bg-accent-500/15 blur-3xl"></div>

        <div class="relative mx-auto max-w-5xl px-4 sm:px-6 text-center">
            {{-- Selo GRÁTIS PARA SEMPRE --}}
            <div class="mb-4 sm:mb-6 inline-flex items-center gap-2 rounded-full border border-brand-300 bg-white/80 px-3 sm:px-4 py-1.5 shadow-soft backdrop-blur-sm dark:border-brand-500/40 dark:bg-slate-900/60">
                <span class="relative flex h-2 w-2 flex-shrink-0">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-brand-500"></span>
                </span>
                <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-brand-700 dark:text-brand-300">
                    100% Grátis · Para sempre · Para todos
                </span>
            </div>

            <h1 class="font-display text-2xl sm:text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight leading-[1.1]">
                Sua próxima <span class="bg-gradient-to-r from-brand-500 to-accent-500 bg-clip-text text-transparent">carreira começa aqui</span><br>
                <span class="text-xl sm:text-3xl md:text-4xl lg:text-5xl text-slate-700 dark:text-slate-300">e nunca vai custar nada.</span>
            </h1>

            <p class="mx-auto mt-4 sm:mt-6 max-w-2xl text-sm sm:text-base md:text-lg text-slate-600 dark:text-slate-300 px-2">
                Recrutamento, feed profissional, IA que otimiza seu currículo, cursos, certificados
                e simulador de entrevistas. <b class="text-brand-700 dark:text-brand-300">Tudo gratuito</b> — pra
                candidatos <b>e</b> empresas. Sem pegadinha, sem "plano premium", sem cartão.
            </p>

            <div class="mt-6 sm:mt-10 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2 sm:gap-3">
                <a href="{{ route('register') }}" class="btn-primary text-sm sm:text-base px-6 sm:px-8 py-3 sm:py-3.5 justify-center">
                    <x-icon name="sparkles" class="w-4 h-4"/>
                    Criar conta grátis
                </a>
                <a href="#recursos" class="btn-secondary text-sm sm:text-base px-6 sm:px-8 py-3 sm:py-3.5 justify-center">
                    Como funciona
                </a>
            </div>

            <div class="mt-4 sm:mt-6 flex flex-wrap items-center justify-center gap-x-4 sm:gap-x-6 gap-y-2 text-[10px] sm:text-xs text-slate-500">
                @foreach ([
                    'Sem cartão de crédito',
                    'Sem limite de vagas',
                    'IA incluída',
                    'Cursos e certificados',
                ] as $bullet)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-3 w-3 sm:h-3.5 sm:w-3.5 text-brand-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        {{ $bullet }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==========================================
         ESTATÍSTICAS — 2x2 mobile, 4x1 desktop
         ========================================== --}}
    <section class="mt-8 sm:mt-10 grid grid-cols-2 gap-2 sm:gap-4 sm:grid-cols-4">
        @foreach ([
            ['label' => 'Profissionais',  'value' => $stats['users'] ?? 0,     'icon' => 'users',     'color' => 'brand'],
            ['label' => 'Vagas ativas',   'value' => $stats['jobs'] ?? 0,      'icon' => 'briefcase', 'color' => 'blue'],
            ['label' => 'Empresas',       'value' => $stats['companies'] ?? 0, 'icon' => 'building',  'color' => 'amber'],
            ['label' => 'Cursos',         'value' => $stats['courses'] ?? 0,   'icon' => 'academic',  'color' => 'violet'],
        ] as $stat)
            @php
                $colorClasses = [
                    'brand'  => 'bg-brand-500/10 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400',
                    'blue'   => 'bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400',
                    'amber'  => 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400',
                    'violet' => 'bg-violet-500/10 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400',
                ][$stat['color']];
            @endphp
            <div class="rounded-2xl bg-white p-3 sm:p-5 shadow-soft ring-1 ring-slate-100 text-center transition hover:-translate-y-0.5 hover:shadow-soft-lg dark:bg-slate-900 dark:ring-slate-800">
                <div class="mx-auto inline-flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-xl {{ $colorClasses }}">
                    <x-icon :name="$stat['icon']" class="h-5 w-5 sm:h-6 sm:w-6"/>
                </div>
                <div class="mt-1 sm:mt-2 font-display text-xl sm:text-3xl font-bold text-brand-600 dark:text-brand-400">
                    {{ number_format($stat['value'], 0, ',', '.') }}
                </div>
                <div class="mt-0.5 sm:mt-1 text-[10px] sm:text-xs uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</div>
            </div>
        @endforeach
    </section>

    {{-- ==========================================
         MANIFESTO — Por que somos gratuitos
         ========================================== --}}
    <section class="mt-10 sm:mt-16 overflow-hidden rounded-2xl sm:rounded-3xl bg-slate-900 p-6 sm:p-10 md:p-14 text-white shadow-soft-lg ring-1 ring-slate-800">
        <div class="mx-auto max-w-3xl text-center">
            <div class="inline-flex items-center gap-2 rounded-full border border-brand-500/40 bg-brand-500/10 px-3 py-1 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-brand-300">
                <x-icon name="heart" class="h-3.5 w-3.5"/> Nosso Manifesto
            </div>
            <h2 class="mt-3 sm:mt-4 font-display text-2xl sm:text-3xl md:text-4xl font-bold leading-tight">
                Emprego é direito, não produto.
            </h2>
            <p class="mx-auto mt-4 sm:mt-6 text-sm sm:text-base md:text-lg leading-relaxed text-slate-300">
                Enquanto plataformas cobram <b class="text-white">R$ 500 – R$ 3.000/mês</b> das empresas
                pra postar vagas, e vendem "planos premium" pra candidatos verem quem viu seu perfil,
                a gente escolheu o caminho difícil: <b class="text-brand-300">tudo grátis, para todos, para sempre</b>.
            </p>
            <p class="mx-auto mt-3 sm:mt-4 text-xs sm:text-sm md:text-base text-slate-400">
                Somos construídos por devs voluntários que acreditam que o Brasil precisa de uma plataforma
                de trabalho que <b class="text-white">não cobra pra você ter oportunidade</b>. Nossa receita futura
                virá de anúncios discretos e ferramentas opcionais — <b class="text-brand-300">nunca de bloquear o essencial</b>.
            </p>

            <div class="mx-auto mt-6 sm:mt-8 grid max-w-2xl gap-2 sm:gap-3 grid-cols-3 text-left">
                @foreach ([
                    ['icon' => 'gift',        'title' => 'Sem plano pago',  'desc' => 'Nada é bloqueado.'],
                    ['icon' => 'no-symbol',   'title' => 'Sem cartão',      'desc' => 'Você nunca cadastra pagamento.'],
                    ['icon' => 'infinity',    'title' => 'Para sempre',     'desc' => 'Compromisso público.'],
                ] as $card)
                    <div class="rounded-xl sm:rounded-2xl bg-white/5 p-2 sm:p-4 ring-1 ring-white/10">
                        <div class="inline-flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-lg bg-brand-500/20 text-brand-300">
                            <x-icon :name="$card['icon']" class="h-4 w-4 sm:h-5 sm:w-5"/>
                        </div>
                        <p class="mt-1.5 sm:mt-2 text-[11px] sm:text-sm font-bold text-white leading-tight">{{ $card['title'] }}</p>
                        <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">{{ $card['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==========================================
         RECURSOS (8 features)
         ========================================== --}}
    <section id="recursos" class="mt-10 sm:mt-16">
        <div class="mb-6 sm:mb-8 text-center px-2">
            <span class="chip bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-300 text-[10px] sm:text-xs">
                Tudo em uma plataforma
            </span>
            <h2 class="mt-3 font-display text-2xl sm:text-3xl md:text-4xl font-bold leading-tight">
                Você quer <span class="text-brand-600">um emprego</span>?<br class="sm:hidden">
                A gente quer <span class="text-accent-500">você contratado.</span>
            </h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm sm:text-base text-slate-600 dark:text-slate-300">
                Não é só um site de vagas. É um ecossistema completo pra você aprender, se conectar e se destacar.
            </p>
        </div>

        <div class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['icon' => 'sparkles',  'title' => 'Assistente IA',           'desc' => 'Gerador de currículo, carta de apresentação, otimização de LinkedIn e simulador de entrevistas.',  'color' => 'from-brand-500 to-emerald-400'],
                ['icon' => 'briefcase', 'title' => 'Vagas com Match',         'desc' => 'IA compara seu perfil com cada vaga e mostra a compatibilidade em porcentagem.',                    'color' => 'from-blue-500 to-sky-400'],
                ['icon' => 'academic',  'title' => 'Cursos & Certificados',   'desc' => 'Trilhas completas com aulas, quizzes e certificado assinado digitalmente ao terminar.',              'color' => 'from-amber-500 to-orange-400'],
                ['icon' => 'clipboard', 'title' => 'Avaliações de Skills',    'desc' => 'Teste seus conhecimentos e ganhe badges verificados que aparecem no seu perfil.',                    'color' => 'from-purple-500 to-fuchsia-400'],
                ['icon' => 'chat',      'title' => 'Feed Profissional',       'desc' => 'Compartilhe conquistas, discuta tendências e siga profissionais da sua área.',                      'color' => 'from-rose-500 to-pink-400'],
                ['icon' => 'target',    'title' => 'Kanban de Vagas',         'desc' => 'Empresas gerenciam candidaturas por etapas visuais. Candidatos veem em qual fase estão.',            'color' => 'from-teal-500 to-cyan-400'],
                ['icon' => 'trophy',    'title' => 'Gamificação',             'desc' => 'Ganhe pontos, suba de nível, colecione badges. Aprender virou jogo.',                                'color' => 'from-yellow-500 to-amber-400'],
                ['icon' => 'message',   'title' => 'Mensagens Diretas',       'desc' => 'Empresas conversam com candidatos, candidatos conversam entre si. Zero fricção.',                    'color' => 'from-indigo-500 to-violet-400'],
            ] as $feature)
                <div class="group relative overflow-hidden rounded-2xl bg-white p-4 sm:p-5 shadow-soft ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-soft-lg dark:bg-slate-900 dark:ring-slate-800">
                    <div class="mb-2 sm:mb-3 inline-flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $feature['color'] }} text-white shadow-soft transition group-hover:scale-110">
                        <x-icon :name="$feature['icon']" class="h-5 w-5 sm:h-6 sm:w-6"/>
                    </div>
                    <h3 class="font-display font-bold text-sm sm:text-base text-slate-900 dark:text-white">{{ $feature['title'] }}</h3>
                    <p class="mt-1 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ==========================================
         COMPARATIVO — nós vs concorrentes
         ========================================== --}}
    <section class="mt-10 sm:mt-16 rounded-2xl sm:rounded-3xl bg-white p-4 sm:p-6 md:p-10 shadow-soft-lg ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
        <div class="mb-6 sm:mb-8 text-center">
            <span class="chip bg-accent-500/10 text-accent-500 text-[10px] sm:text-xs">
                Por que somos diferentes
            </span>
            <h2 class="mt-3 font-display text-2xl sm:text-3xl md:text-4xl font-bold">
                Compare e decida.
            </h2>
        </div>

        <div class="-mx-4 sm:mx-0 overflow-x-auto">
            <table class="w-full min-w-[520px] text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700">
                        <th class="py-2 sm:py-3 pl-4 sm:pl-0 text-left font-semibold text-slate-500">Recurso</th>
                        <th class="py-2 sm:py-3 text-center">
                            <span class="inline-block rounded-lg sm:rounded-xl bg-brand-500 px-2 sm:px-3 py-0.5 sm:py-1 text-[10px] sm:text-xs font-bold text-white whitespace-nowrap">SocialJobs</span>
                        </th>
                        <th class="py-2 sm:py-3 pr-4 sm:pr-0 text-center font-semibold text-slate-500 text-[10px] sm:text-sm">Outras</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ([
                        ['label' => 'Publicar vaga', 'us' => 'grátis', 'them' => 'R$ 500–3.000/mês'],
                        ['label' => 'Aplicar em vagas', 'us' => 'grátis', 'them' => 'grátis'],
                        ['label' => 'Ver quem viu meu perfil', 'us' => 'grátis', 'them' => 'plano premium'],
                        ['label' => 'IA otimizar currículo', 'us' => 'grátis', 'them' => 'raramente disponível'],
                        ['label' => 'Simulador de entrevista', 'us' => 'grátis', 'them' => 'não existe'],
                        ['label' => 'Cursos + certificados', 'us' => 'grátis', 'them' => 'plataforma paga'],
                        ['label' => 'Kanban de candidaturas', 'us' => 'grátis', 'them' => 'plano empresarial'],
                        ['label' => 'Match por IA', 'us' => 'grátis', 'them' => 'plano premium'],
                        ['label' => 'Mensagens ilimitadas', 'us' => 'grátis', 'them' => 'limitado no free'],
                    ] as $row)
                        <tr class="text-slate-700 dark:text-slate-300">
                            <td class="py-2 sm:py-3 pl-4 sm:pl-0 font-medium">{{ $row['label'] }}</td>
                            <td class="py-2 sm:py-3 text-center">
                                <span class="inline-flex items-center gap-1 font-bold text-brand-600 dark:text-brand-400">
                                    <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                                    {{ $row['us'] }}
                                </span>
                            </td>
                            <td class="py-2 sm:py-3 pr-4 sm:pr-0 text-center text-slate-500 text-[11px] sm:text-sm">{{ $row['them'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- ==========================================
         DUAS PORTAS — Candidato / Empresa
         ========================================== --}}
    <section class="mt-10 sm:mt-16 grid gap-4 sm:gap-6 md:grid-cols-2">
        {{-- Card Candidato --}}
        <div class="group relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-br from-brand-500 to-emerald-600 p-6 sm:p-8 text-white shadow-soft-lg">
            <div class="absolute -right-8 -top-8 h-32 w-32 sm:h-40 sm:w-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative">
                <div class="mb-3 sm:mb-4 inline-flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-2xl bg-white/15 text-white backdrop-blur-sm">
                    <x-icon name="user" class="h-6 w-6 sm:h-7 sm:w-7"/>
                </div>
                <h3 class="font-display text-xl sm:text-2xl font-bold">Sou candidato</h3>
                <p class="mt-2 text-sm sm:text-base text-white/90">
                    Quero encontrar meu próximo emprego, aprender coisas novas e me destacar.
                </p>
                <ul class="mt-4 sm:mt-5 space-y-1.5 sm:space-y-2 text-xs sm:text-sm">
                    <li class="flex gap-2"><span class="flex-shrink-0">✓</span><span>Perfil profissional gratuito para sempre</span></li>
                    <li class="flex gap-2"><span class="flex-shrink-0">✓</span><span>IA cria seu currículo em 30 segundos</span></li>
                    <li class="flex gap-2"><span class="flex-shrink-0">✓</span><span>Cursos + certificados verificados</span></li>
                    <li class="flex gap-2"><span class="flex-shrink-0">✓</span><span>Simulador de entrevista com feedback</span></li>
                </ul>
                <a href="{{ route('register') }}" class="mt-5 sm:mt-6 inline-flex items-center gap-2 rounded-xl sm:rounded-2xl bg-white px-5 sm:px-6 py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-brand-700 shadow-soft transition hover:bg-slate-50">
                    Começar como candidato
                    <x-icon name="arrow-right" class="w-4 h-4"/>
                </a>
            </div>
        </div>

        {{-- Card Empresa --}}
        <div class="group relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 p-6 sm:p-8 text-white shadow-soft-lg ring-1 ring-slate-700">
            <div class="absolute -right-8 -top-8 h-32 w-32 sm:h-40 sm:w-40 rounded-full bg-accent-500/20 blur-2xl"></div>
            <div class="relative">
                <div class="mb-3 sm:mb-4 inline-flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-2xl bg-accent-500/20 text-accent-500 backdrop-blur-sm">
                    <x-icon name="building" class="h-6 w-6 sm:h-7 sm:w-7"/>
                </div>
                <h3 class="font-display text-xl sm:text-2xl font-bold">Sou empresa</h3>
                <p class="mt-2 text-sm sm:text-base text-slate-300">
                    Quero contratar bons profissionais sem pagar plano mensal absurdo.
                </p>
                <ul class="mt-4 sm:mt-5 space-y-1.5 sm:space-y-2 text-xs sm:text-sm text-slate-200">
                    <li class="flex gap-2"><span class="text-accent-500 flex-shrink-0">✓</span><span>Publique quantas vagas quiser, grátis</span></li>
                    <li class="flex gap-2"><span class="text-accent-500 flex-shrink-0">✓</span><span>Kanban visual pra gerenciar candidaturas</span></li>
                    <li class="flex gap-2"><span class="text-accent-500 flex-shrink-0">✓</span><span>Filtros por skill, localização e match</span></li>
                    <li class="flex gap-2"><span class="text-accent-500 flex-shrink-0">✓</span><span>Chat direto com candidatos</span></li>
                </ul>
                <a href="{{ route('register') }}" class="mt-5 sm:mt-6 inline-flex items-center gap-2 rounded-xl sm:rounded-2xl bg-accent-500 px-5 sm:px-6 py-2.5 sm:py-3 text-xs sm:text-sm font-bold text-white shadow-soft transition hover:bg-accent-600">
                    Cadastrar empresa
                    <x-icon name="arrow-right" class="w-4 h-4"/>
                </a>
            </div>
        </div>
    </section>

    {{-- ==========================================
         VAGAS EM DESTAQUE
         ========================================== --}}
    <section id="vagas" class="mt-10 sm:mt-16">
        <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row items-start sm:items-end justify-between gap-3">
            <div>
                <span class="chip bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-300 text-[10px] sm:text-xs">Ao vivo</span>
                <h2 class="mt-2 font-display text-xl sm:text-2xl md:text-3xl font-bold">Últimas vagas</h2>
                <p class="text-xs sm:text-sm text-slate-500">Publicadas pelas melhores empresas — todas grátis pra aplicar.</p>
            </div>
            <a href="{{ url('/jobs') }}" class="btn-ghost text-xs sm:text-sm self-end sm:self-auto">Ver todas <x-icon name="arrow-right" class="w-4 h-4"/></a>
        </div>

        <div class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($latestJobs as $job)
                <x-card class="transition hover:-translate-y-0.5 hover:shadow-soft-lg">
                    <div class="flex items-start gap-3">
                        <div class="grid h-10 w-10 sm:h-12 sm:w-12 place-items-center rounded-xl sm:rounded-2xl bg-gradient-to-br from-brand-100 to-brand-200 text-brand-700 font-display font-bold text-base sm:text-lg dark:from-brand-500/20 dark:to-brand-500/10 dark:text-brand-300 flex-shrink-0">
                            {{ mb_substr($job->company_name ?? $job->title ?? 'C', 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate font-semibold text-sm sm:text-base">{{ $job->title ?? 'Vaga' }}</h3>
                            <p class="truncate text-[11px] sm:text-xs text-slate-500">{{ $job->company_name ?? 'Empresa' }} · {{ $job->location ?? 'Remoto' }}</p>
                        </div>
                    </div>
                    <p class="mt-2 sm:mt-3 line-clamp-3 text-xs sm:text-sm text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit(strip_tags((string) ($job->description ?? '')), 140) }}</p>
                    <div class="mt-3 sm:mt-4 flex items-center gap-2 flex-wrap">
                        <x-chip color="emerald">{{ $job->contract_type ?? 'CLT' }}</x-chip>
                        <x-chip color="sky">{{ $job->modality ?? 'Remoto' }}</x-chip>
                    </div>
                </x-card>
            @empty
                <div class="col-span-full card text-center py-8">
                    <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-500/10 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                        <x-icon name="rocket" class="h-6 w-6"/>
                    </div>
                    <p class="mt-3 font-bold text-sm sm:text-base">Ainda não há vagas publicadas.</p>
                    <p class="mt-1 text-xs sm:text-sm text-slate-500">Seja uma das primeiras empresas a publicar!</p>
                    <a href="{{ route('register') }}" class="btn-primary mt-4 text-xs sm:text-sm">Cadastrar empresa</a>
                </div>
            @endforelse
        </div>
    </section>

    {{-- ==========================================
         FAQ
         ========================================== --}}
    <section class="mt-10 sm:mt-16 rounded-2xl sm:rounded-3xl bg-white p-4 sm:p-6 md:p-10 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
        <div class="mx-auto max-w-3xl">
            <div class="mb-6 sm:mb-8 text-center">
                <span class="chip bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 text-[10px] sm:text-xs">Perguntas frequentes</span>
                <h2 class="mt-3 font-display text-2xl sm:text-3xl md:text-4xl font-bold leading-tight">
                    "É grátis mesmo? Qual a pegadinha?"
                </h2>
                <p class="mt-2 text-sm sm:text-base text-slate-600 dark:text-slate-300">
                    Nenhuma. Aqui estão as respostas mais honestas que você vai ouvir sobre uma plataforma de recrutamento.
                </p>
            </div>

            <div class="space-y-2 sm:space-y-3" x-data="{ open: 0 }">
                @foreach ([
                    ['q' => 'É realmente 100% grátis? Vai começar a cobrar depois?', 'a' => 'É realmente grátis, para candidatos e empresas, sem limite de uso das funcionalidades essenciais. Nosso compromisso público é: as funções principais (publicar vaga, aplicar, mensagem, currículo, cursos, match) serão gratuitas PARA SEMPRE. No futuro podemos ter anúncios discretos ou ferramentas opcionais (ex: destaque de vaga por 24h) — mas nada essencial vai virar pago.'],
                    ['q' => 'Como vocês pagam os custos do servidor então?', 'a' => 'Somos construídos por devs voluntários que acreditam no projeto. Os custos operacionais (servidor, IA) são cobertos por doações voluntárias e futuros anúncios opcionais. Zero cobrança dos usuários pra usar o essencial.'],
                    ['q' => 'Empresas precisam pagar pra publicar vagas?', 'a' => 'Não. Empresas publicam quantas vagas quiserem, gratuitamente, sem cadastrar cartão. Kanban de candidaturas, filtros por skill, chat com candidatos — tudo incluso.'],
                    ['q' => 'A IA (assistente, gerador de currículo) é grátis também?', 'a' => 'Sim. Você pode usar quantas vezes quiser: gerador de currículo, carta de apresentação, otimização de LinkedIn, análise de vaga, simulador de entrevista.'],
                    ['q' => 'Meus dados vão ser vendidos?', 'a' => 'Não. Nunca vendemos dados de usuários. Você tem controle total: pode exportar tudo que temos sobre você ou deletar sua conta a qualquer momento (LGPD).'],
                    ['q' => 'Posso confiar nos certificados dos cursos?', 'a' => 'Sim. Cada certificado tem um código único verificável publicamente. Empresas podem escanear ou digitar o código e conferir a autenticidade.'],
                    ['q' => 'É seguro pra empresas pequenas ou informais?', 'a' => 'Perfeito pra qualquer tamanho. Um MEI que precisa contratar um estagiário paga o mesmo que uma multinacional: zero. Sem contrato, sem letras miúdas.'],
                ] as $i => $item)
                    <div class="rounded-xl sm:rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50">
                        <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}"
                                class="flex w-full items-center justify-between gap-3 sm:gap-4 p-3 sm:p-4 text-left">
                            <span class="font-semibold text-sm sm:text-base text-slate-900 dark:text-white leading-tight">{{ $item['q'] }}</span>
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0 text-brand-500 transition-transform duration-200"
                                 :class="{ 'rotate-180': open === {{ $i }} }"
                                 viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.3 7.3a1 1 0 011.4 0L10 10.6l3.3-3.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 010-1.4z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse x-cloak>
                            <p class="border-t border-slate-200 p-3 sm:p-4 text-xs sm:text-sm text-slate-600 dark:border-slate-700 dark:text-slate-300 leading-relaxed">
                                {{ $item['a'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==========================================
         CTA FINAL
         ========================================== --}}
    <section class="relative mt-10 sm:mt-16 overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-br from-brand-500 via-brand-600 to-accent-500 p-6 sm:p-10 md:p-14 text-center text-white shadow-soft-lg">
        <div class="pointer-events-none absolute inset-0 opacity-30">
            <div class="absolute -left-16 -top-16 h-40 w-40 sm:h-56 sm:w-56 rounded-full bg-white/20 blur-3xl"></div>
            <div class="absolute -right-16 -bottom-16 h-40 w-40 sm:h-56 sm:w-56 rounded-full bg-white/20 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-3xl">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1 text-[10px] sm:text-xs font-bold uppercase tracking-wider backdrop-blur-sm">
                <x-icon name="bolt" class="h-3.5 w-3.5"/> Comece agora
            </div>
            <h2 class="mt-3 sm:mt-4 font-display text-2xl sm:text-4xl md:text-5xl font-bold leading-tight">
                Sua carreira não pode esperar.
            </h2>
            <p class="mx-auto mt-3 sm:mt-4 max-w-xl text-sm sm:text-base md:text-lg opacity-95">
                Junte-se a milhares de profissionais e empresas que já entenderam que oportunidade
                não tem preço — e nem deveria ter.
            </p>
            <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2 sm:gap-3">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-xl sm:rounded-2xl bg-white px-6 sm:px-8 py-3 sm:py-3.5 text-xs sm:text-sm font-bold text-brand-700 shadow-soft transition hover:bg-slate-50 hover:shadow-soft-lg">
                    Criar conta grátis
                    <x-icon name="arrow-right" class="w-4 h-4"/>
                </a>
                <a href="{{ url('/jobs') }}" class="inline-flex items-center justify-center gap-2 rounded-xl sm:rounded-2xl border-2 border-white/50 bg-white/10 px-6 sm:px-8 py-3 sm:py-3.5 text-xs sm:text-sm font-bold text-white backdrop-blur-sm transition hover:bg-white/20">
                    Ver vagas primeiro
                </a>
            </div>
            <p class="mt-4 sm:mt-6 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-[10px] sm:text-xs opacity-80">
                <span class="inline-flex items-center gap-1"><x-icon name="bolt" class="h-3 w-3"/> Cadastro em 30 segundos</span>
                <span class="inline-flex items-center gap-1"><x-icon name="no-symbol" class="h-3 w-3"/> Sem cartão</span>
                <span class="inline-flex items-center gap-1"><x-icon name="infinity" class="h-3 w-3"/> Grátis para sempre</span>
            </p>
        </div>
    </section>
</x-app-layout>
