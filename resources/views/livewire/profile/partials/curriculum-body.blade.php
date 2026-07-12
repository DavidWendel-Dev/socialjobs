@php
    /**
     * View parcial reutilizável do Currículo Digital.
     * Recebe as chaves de $cv retornadas por CurriculumService::buildFor().
     *
     * Uso:
     *  - Modo "público" (rota /cv/{username}): incluído em curriculum-public.blade.php
     *  - Modo "embutido" (aba dentro do perfil autenticado)
     *
     * Não usa header/footer — só o conteúdo do CV.
     */
    $u          = $user;
    $stats      = $stats;
    $links      = $about['links'];
    $username   = $u->username ?? $u->id;

    // Selo "Verificado por SocialJobs" reutilizado em cursos e entrevistas
    $verifiedBadge = '<span class="inline-flex items-center gap-1 rounded-full bg-brand-500/10 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-brand-700 dark:text-brand-300"><svg class="h-2.5 w-2.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 4 5v7c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V5l-8-3zm-1 15L7 13l1.4-1.4L11 14.2l4.6-4.6L17 11l-6 6z"/></svg>Verificado</span>';
@endphp

<div class="print-avoid-break">
    {{-- ============================================================
         HEADER — Capa + identidade + XP/level em destaque
         ============================================================ --}}
    <div class="relative overflow-hidden">
        @if ($u->cover_path)
            <div class="h-32 sm:h-48"
                 style="background-image:url('{{ $u->cover_url }}');background-size:cover;background-position:center"></div>
        @else
            <div class="relative h-32 bg-gradient-to-br from-brand-500 via-brand-600 to-accent sm:h-48">
                <div class="absolute inset-0 opacity-25"
                     style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,.5) 0, transparent 40%), radial-gradient(circle at 80% 60%, rgba(255,255,255,.3) 0, transparent 40%);"></div>
            </div>
        @endif

        <div class="px-6 pb-6 sm:px-10 sm:pb-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end">
                {{-- Avatar --}}
                <div class="relative -mt-14 shrink-0 sm:-mt-20">
                    <x-avatar :user="$u" size="lg"
                              class="!h-28 !w-28 !text-3xl ring-4 ring-white shadow-soft dark:ring-slate-900 sm:!h-32 sm:!w-32 sm:!text-4xl"/>
                    @if ($about['open_to_work'])
                        <span class="absolute -bottom-1 -right-1 grid h-8 w-8 place-items-center rounded-full bg-brand-500 ring-4 ring-white dark:ring-slate-900"
                              title="Aberto a oportunidades">
                            <x-icon name="briefcase" class="h-4 w-4 text-white"/>
                        </span>
                    @endif
                </div>

                {{-- Nome + headline --}}
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <h1 class="font-display text-2xl font-bold leading-tight sm:text-3xl">
                            {{ $about['name'] }}
                        </h1>
                        <span class="inline-flex items-center gap-1 rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2 4 5v7c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V5l-8-3z"/>
                            </svg>
                            Verificado
                        </span>
                    </div>
                    @if ($about['headline'])
                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-300 sm:text-base">
                            {{ $about['headline'] }}
                        </p>
                    @endif
                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                        @if ($about['location'])
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0Z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                {{ $about['location'] }}
                            </span>
                        @endif
                        @if ($about['open_to_work'])
                            <span class="inline-flex items-center gap-1 rounded-full bg-brand-500/10 px-2 py-0.5 font-medium text-brand-700 dark:text-brand-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                                Aberto a oportunidades
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Links externos --}}
            @if ($links['linkedin'] || $links['github'] || $links['portfolio'])
                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($links['linkedin'])
                        <a href="{{ $links['linkedin'] }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                            <svg class="h-3.5 w-3.5 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.5 2h-17A1.5 1.5 0 0 0 2 3.5v17A1.5 1.5 0 0 0 3.5 22h17a1.5 1.5 0 0 0 1.5-1.5v-17A1.5 1.5 0 0 0 20.5 2ZM8 19H5v-9h3v9Zm-1.5-10.28A1.72 1.72 0 1 1 8.22 7 1.72 1.72 0 0 1 6.5 8.72ZM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0 0 13 14.19a.66.66 0 0 0 0 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 0 1 2.7-1.4c1.55 0 3.36.86 3.36 3.66Z"/>
                            </svg>
                            LinkedIn
                        </a>
                    @endif
                    @if ($links['github'])
                        <a href="{{ $links['github'] }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 .3a12 12 0 0 0-3.8 23.38c.6.12.83-.26.83-.58v-2.24c-3.34.73-4.03-1.43-4.03-1.43-.55-1.4-1.34-1.77-1.34-1.77-1.1-.75.08-.73.08-.73 1.21.09 1.85 1.25 1.85 1.25 1.08 1.86 2.84 1.32 3.53 1.01.1-.78.42-1.32.76-1.62-2.66-.31-5.46-1.34-5.46-5.96 0-1.32.47-2.4 1.24-3.24-.12-.31-.54-1.53.12-3.18 0 0 1-.32 3.3 1.24a11.5 11.5 0 0 1 6 0c2.28-1.56 3.29-1.24 3.29-1.24.66 1.65.24 2.87.12 3.18a4.68 4.68 0 0 1 1.24 3.24c0 4.63-2.8 5.65-5.48 5.95.43.37.81 1.1.81 2.22v3.29c0 .32.22.71.83.58A12 12 0 0 0 12 .3Z"/>
                            </svg>
                            GitHub
                        </a>
                    @endif
                    @if ($links['portfolio'])
                        <a href="{{ $links['portfolio'] }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                            <svg class="h-3.5 w-3.5 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                            </svg>
                            Portfólio
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================================
         MÉTRICAS AGREGADAS — cards com números
         ============================================================ --}}
    <div class="grid grid-cols-2 gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-5 dark:border-slate-800 dark:bg-slate-800/30 sm:grid-cols-4 sm:px-10">
        <div class="text-center">
            <div class="font-display text-2xl font-bold text-brand-600 sm:text-3xl">
                {{ $stats['level'] }}
            </div>
            <p class="text-[10px] font-medium uppercase tracking-wider text-slate-500">Nível</p>
        </div>
        <div class="text-center">
            <div class="font-display text-2xl font-bold sm:text-3xl">
                {{ number_format($stats['total_xp'], 0, ',', '.') }}
            </div>
            <p class="text-[10px] font-medium uppercase tracking-wider text-slate-500">XP total</p>
        </div>
        <div class="text-center">
            <div class="font-display text-2xl font-bold sm:text-3xl">
                {{ $stats['courses_count'] }}
            </div>
            <p class="text-[10px] font-medium uppercase tracking-wider text-slate-500">Cursos</p>
        </div>
        <div class="text-center">
            <div class="font-display text-2xl font-bold sm:text-3xl">
                @if ($stats['avg_interview'])
                    {{ $stats['avg_interview'] }}<span class="text-sm text-slate-400">/100</span>
                @else
                    —
                @endif
            </div>
            <p class="text-[10px] font-medium uppercase tracking-wider text-slate-500">Entrevistas</p>
        </div>
    </div>
</div>

<div class="space-y-8 px-6 py-8 sm:px-10">
    {{-- ============================================================
         SOBRE
         ============================================================ --}}
    @if ($about['bio'])
        <section class="print-avoid-break">
            <h2 class="mb-3 flex items-center gap-2 font-display text-lg font-bold">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                    <x-icon name="user" class="h-4 w-4"/>
                </span>
                Sobre
            </h2>
            <p class="whitespace-pre-line text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                {{ $about['bio'] }}
            </p>
        </section>
    @endif

    {{-- ============================================================
         EXPERIÊNCIA + HABILIDADES lado a lado (2 colunas em md+)
         Em mobile empilha naturalmente.
         ============================================================ --}}
    <div class="grid gap-8 md:grid-cols-2">
        {{-- EXPERIÊNCIAS PROFISSIONAIS — timeline --}}
        @if ($experiences->count())
            <section class="print-avoid-break">
                <h2 class="mb-4 flex items-center gap-2 font-display text-lg font-bold">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                        <x-icon name="briefcase" class="h-4 w-4"/>
                    </span>
                    Experiência profissional
                </h2>
                <ol class="relative space-y-5 border-l-2 border-slate-100 pl-6 dark:border-slate-800">
                    @foreach ($experiences as $exp)
                        <li class="relative">
                            <span class="absolute -left-[29px] top-1.5 grid h-4 w-4 place-items-center rounded-full bg-brand-500 ring-4 ring-white dark:ring-slate-900"></span>
                            <p class="font-semibold">{{ $exp->role ?? 'Cargo' }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                {{ $exp->company_name ?? '' }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ optional($exp->start_date)->format('m/Y') ?? '—' }}
                                —
                                @if ($exp->current)
                                    <span class="text-brand-600">Atual</span>
                                @else
                                    {{ optional($exp->end_date)->format('m/Y') ?? '—' }}
                                @endif
                            </p>
                            @if ($exp->description)
                                <p class="mt-2 whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">
                                    {{ $exp->description }}
                                </p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        {{-- SKILLS — chips --}}
        @if ($skills->count())
            <section class="print-avoid-break">
                <h2 class="mb-3 flex items-center gap-2 font-display text-lg font-bold">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                        <x-icon name="sparkles" class="h-4 w-4"/>
                    </span>
                    Habilidades
                </h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($skills as $s)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                            {{ $s->name }}
                        </span>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- ============================================================
         FORMAÇÃO + PORTFÓLIO lado a lado (2 colunas em md+)
         ============================================================ --}}
    <div class="grid gap-8 md:grid-cols-2">
        {{-- FORMAÇÃO --}}
        @if ($educations->count())
            <section class="print-avoid-break">
                <h2 class="mb-4 flex items-center gap-2 font-display text-lg font-bold">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                        <x-icon name="academic" class="h-4 w-4"/>
                    </span>
                    Formação acadêmica
                </h2>
                <ol class="relative space-y-4 border-l-2 border-slate-100 pl-6 dark:border-slate-800">
                    @foreach ($educations as $ed)
                        <li class="relative">
                            <span class="absolute -left-[29px] top-1.5 h-4 w-4 rounded-full bg-accent ring-4 ring-white dark:ring-slate-900"></span>
                            <p class="font-semibold">{{ $ed->degree ?? 'Curso' }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $ed->institution ?? '' }}</p>
                            <p class="text-xs text-slate-500">
                                {{ optional($ed->start_date)->format('Y') }}
                                —
                                {{ optional($ed->end_date)->format('Y') ?? 'Presente' }}
                            </p>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        {{-- PORTFÓLIO --}}
        @if ($portfolio->count())
            <section class="print-avoid-break">
                <h2 class="mb-4 flex items-center gap-2 font-display text-lg font-bold">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                        <x-icon name="briefcase" class="h-4 w-4"/>
                    </span>
                    Portfólio
                </h2>
                <div class="space-y-2">
                    @foreach ($portfolio as $item)
                        <a href="{{ $item->url ?? '#' }}" target="_blank" rel="noopener"
                           class="block rounded-xl border border-slate-100 p-3 transition hover:border-brand-500 dark:border-slate-800 dark:hover:border-brand-500">
                            <p class="text-sm font-semibold">{{ $item->title }}</p>
                            @if ($item->description)
                                <p class="mt-0.5 line-clamp-2 text-xs text-slate-500">{{ $item->description }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- ============================================================
         BADGES DE SKILLS VERIFICADOS (SKILL ASSESSMENTS)
         Grid de "medalhas" — cada teste aprovado vira um card com score.
         Esse é o diferencial: skill CONFIRMADA por prova, não só declarada.
         ============================================================ --}}
    @if (! empty($skill_badges) && $skill_badges->count())
        <section class="print-avoid-break">
            <h2 class="mb-4 flex items-center gap-2 font-display text-lg font-bold">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2 4 5v7c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V5l-8-3z"/>
                    </svg>
                </span>
                Habilidades verificadas por teste
                <span class="text-xs font-normal text-slate-500">
                    ({{ $skill_badges->count() }})
                </span>
            </h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($skill_badges as $b)
                    @php
                        $sa = $b['assessment'];
                        $score = $b['best_score'];
                        $scoreColor = $score >= 90 ? 'bg-brand-500' : ($score >= 80 ? 'bg-blue-500' : 'bg-amber-500');
                    @endphp
                    <div class="rounded-xl border border-slate-100 p-3 dark:border-slate-800">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $sa->title }}</p>
                                <p class="truncate text-[10px] text-slate-500">
                                    {{ $sa->category }} · {{ $sa->difficultyLabel() }}
                                </p>
                            </div>
                            {!! $verifiedBadge !!}
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-1.5 rounded-full {{ $scoreColor }}"
                                     style="width: {{ $score }}%"></div>
                            </div>
                            <span class="font-mono text-xs font-bold">
                                {{ $score }}<span class="text-slate-400">/100</span>
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================================================
         CURSOS CONCLUÍDOS NA PLATAFORMA (VERIFICADOS)
         ============================================================ --}}
    @if ($courses_completed->count())
        <section class="print-avoid-break">
            <h2 class="mb-4 flex items-center gap-2 font-display text-lg font-bold">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                    <x-icon name="check" class="h-4 w-4"/>
                </span>
                Cursos concluídos
                <span class="text-xs font-normal text-slate-500">
                    ({{ $courses_completed->count() }} · {{ $stats['total_hours'] }}h)
                </span>
            </h2>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($courses_completed as $enroll)
                    @php $course = $enroll->course; @endphp
                    <div class="rounded-xl border border-slate-100 p-3 dark:border-slate-800">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold">{{ $course->title }}</p>
                            {!! $verifiedBadge !!}
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">
                            @if ($course->level) {{ ucfirst($course->level) }} @endif
                            @if ($course->total_minutes)
                                · {{ round($course->total_minutes / 60, 1) }}h
                            @endif
                            · Concluído em {{ optional($enroll->completed_at)->format('m/Y') }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================================================
         ENTREVISTAS SIMULADAS (VERIFICADAS)
         ============================================================ --}}
    @if ($interviews->count())
        <section class="print-avoid-break">
            <h2 class="mb-4 flex items-center gap-2 font-display text-lg font-bold">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                    <x-icon name="mic" class="h-4 w-4"/>
                </span>
                Simulações de entrevista
                @if ($stats['avg_interview'])
                    <span class="text-xs font-normal text-slate-500">
                        (média {{ $stats['avg_interview'] }}/100)
                    </span>
                @endif
            </h2>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($interviews as $iv)
                    @php
                        $score = (int) $iv->overall_score;
                        $scoreColor = $score >= 80 ? 'bg-brand-500' : ($score >= 60 ? 'bg-blue-500' : 'bg-amber-500');
                    @endphp
                    <div class="rounded-xl border border-slate-100 p-3 dark:border-slate-800">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold">{{ $iv->role_title }}</p>
                            {!! $verifiedBadge !!}
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ ucfirst($iv->seniority) }} ·
                            {{ ucfirst($iv->mode) === 'Text' ? 'Texto' : 'Voz' }} ·
                            {{ optional($iv->finished_at)->format('m/Y') }}
                        </p>
                        <div class="mt-2 flex items-center gap-2">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-1.5 rounded-full {{ $scoreColor }}"
                                     style="width: {{ $score }}%"></div>
                            </div>
                            <span class="font-mono text-xs font-bold">{{ $score }}<span class="text-slate-400">/100</span></span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================================================
         POSTS EM DESTAQUE (o candidato marca no compositor)
         ============================================================ --}}
    @if ($featured_posts->count())
        <section class="print-avoid-break">
            <h2 class="mb-4 flex items-center gap-2 font-display text-lg font-bold">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                    <x-icon name="sparkles" class="h-4 w-4"/>
                </span>
                Publicações em destaque
            </h2>
            <div class="space-y-2">
                @foreach ($featured_posts as $post)
                    <a href="{{ route('posts.show', $post) }}" target="_blank"
                       class="block rounded-xl border border-slate-100 p-3 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50">
                        <p class="line-clamp-3 text-sm text-slate-700 dark:text-slate-200">
                            {{ \Illuminate\Support\Str::limit(strip_tags($post->body), 240) }}
                        </p>
                        <p class="mt-1 text-[10px] text-slate-400">
                            {{ optional($post->created_at)->format('d M Y') }}
                        </p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Rodapé com selo de verificação --}}
    <div class="mt-8 border-t border-slate-100 pt-6 text-center dark:border-slate-800">
        <div class="inline-flex items-center gap-2 text-xs text-slate-500">
            <svg class="h-4 w-4 text-brand-500" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2 4 5v7c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V5l-8-3z"/>
            </svg>
            <span>
                Currículo verificado por
                <a href="{{ url('/') }}" class="font-semibold text-brand-600 hover:underline">SocialJobs</a>
                · Gerado em {{ now()->format('d/m/Y') }}
            </span>
        </div>
    </div>
</div>
