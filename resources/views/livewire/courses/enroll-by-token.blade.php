<div class="mx-auto max-w-2xl">
    @if ($errorState === 'not_found')
        <div class="card text-center">
            <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300">
                <x-icon name="x" class="h-7 w-7"/>
            </div>
            <h1 class="font-display text-xl font-bold">Convite inválido</h1>
            <p class="mt-1 text-sm text-slate-500">Este link de convite não existe ou foi removido. Confira com a empresa que enviou.</p>
            <a href="{{ route('feed') }}" class="mt-4 inline-block rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                Voltar ao início
            </a>
        </div>
    @elseif ($errorState === 'needs_login')
        <div class="card text-center">
            @if ($course?->companyProfile?->logo_path)
                <img src="{{ $course->companyProfile->logo_path }}" alt="" class="mx-auto mb-3 h-14 w-14 rounded-2xl object-cover">
            @else
                <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-brand-100 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                    <x-icon name="academic" class="h-7 w-7"/>
                </div>
            @endif
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Convite de curso</p>
            <h1 class="mt-1 font-display text-2xl font-bold">{{ $course?->title }}</h1>
            @if ($course?->companyProfile)
                <p class="mt-1 text-sm text-slate-500">
                    da empresa <strong>{{ $course->companyProfile->trade_name ?: $course->companyProfile->legal_name }}</strong>
                </p>
            @endif
            @if ($course?->summary)
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">{{ $course->summary }}</p>
            @endif

            <div class="mt-5 rounded-2xl bg-brand-50 p-4 text-sm text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                Para acessar este curso, faça login ou cadastre-se como candidato. Você será matriculado automaticamente.
            </div>

            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-center">
                <a href="{{ route('login') }}" class="btn-primary inline-flex items-center justify-center gap-2">
                    <x-icon name="user" class="h-4 w-4"/>
                    Entrar
                </a>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-brand-500 px-4 py-2 text-sm font-bold text-brand-700 hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/10">
                    <x-icon name="sparkles" class="h-4 w-4"/>
                    Criar conta grátis
                </a>
            </div>
        </div>
    @elseif ($errorState === 'company_user')
        <div class="card text-center">
            <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">
                <x-icon name="x" class="h-7 w-7"/>
            </div>
            <h1 class="font-display text-xl font-bold">Somente candidatos</h1>
            <p class="mt-1 text-sm text-slate-500">Este convite é para candidatos. Contas de empresa não podem se matricular em cursos.</p>
        </div>
    @else
        {{-- Fluxo normal: já foi redirecionado; mostra fallback --}}
        <div class="card text-center">
            <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                <x-icon name="check" class="h-7 w-7"/>
            </div>
            <h1 class="font-display text-xl font-bold">Redirecionando…</h1>
            <p class="mt-1 text-sm text-slate-500">Você foi matriculado com sucesso.</p>
        </div>
    @endif
</div>
