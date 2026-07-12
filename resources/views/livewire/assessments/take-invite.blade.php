<div class="mx-auto max-w-2xl">
    @if ($errorState === 'not_found')
        <div class="rounded-3xl bg-white p-8 text-center shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-500/15">
                <x-icon name="x" class="h-7 w-7"/>
            </div>
            <p class="font-display text-lg font-bold">Convite inválido</p>
            <p class="mt-1 text-sm text-slate-500">
                O link que você acessou não existe ou foi removido. Peça um novo convite à empresa.
            </p>
        </div>

    @elseif ($errorState === 'expired')
        <div class="rounded-3xl bg-white p-8 text-center shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-500/15">
                <x-icon name="check" class="h-7 w-7"/>
            </div>
            <p class="font-display text-lg font-bold">Convite expirado</p>
            <p class="mt-1 text-sm text-slate-500">
                Este convite passou do prazo. Peça um novo à empresa que enviou.
            </p>
        </div>

    @elseif ($errorState === 'completed')
        <div class="rounded-3xl bg-white p-8 text-center shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15">
                <x-icon name="check" class="h-7 w-7"/>
            </div>
            <p class="font-display text-lg font-bold">Teste já concluído</p>
            <p class="mt-1 text-sm text-slate-500">
                Você já finalizou este teste. A empresa já tem o seu resultado.
            </p>
            @auth
                <a href="{{ url('/u/' . (auth()->user()->username ?? auth()->user()->id) . '?tab=curriculum') }}"
                   class="mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-bold text-white hover:bg-brand-600">
                    Ver meu Currículo →
                </a>
            @endauth
        </div>

    @elseif ($errorState === 'needs_login')
        <div class="overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            <div class="bg-gradient-to-br from-brand-500 to-accent p-6 text-white sm:p-8">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider opacity-90">
                    <x-icon name="sparkles" class="h-3.5 w-3.5"/>
                    Você foi convidado para um teste
                </div>
                <h1 class="mt-1 font-display text-xl font-bold sm:text-2xl">
                    {{ $invitation?->assessment?->title ?? 'Teste de proficiência' }}
                </h1>
                @if ($invitation?->companyProfile)
                    <p class="mt-2 text-sm opacity-90">
                        Convite de <strong>{{ $invitation->companyProfile->trade_name ?? $invitation->companyProfile->legal_name }}</strong>
                    </p>
                @endif
            </div>
            <div class="space-y-3 p-6">
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    Faça login ou crie uma conta gratuita para começar. Após entrar você é direcionado direto ao teste.
                </p>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <a href="{{ route('login') }}"
                       class="flex-1 rounded-xl bg-brand-500 px-4 py-2.5 text-center text-sm font-bold text-white hover:bg-brand-600">
                        Já tenho conta — Entrar
                    </a>
                    <a href="{{ route('register') }}"
                       class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        Sou novo — Cadastrar
                    </a>
                </div>
                <p class="text-[11px] text-slate-500">
                    Após o login, retornaremos você automaticamente para este teste.
                </p>
            </div>
        </div>
    @endif
</div>
