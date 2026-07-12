<div class="space-y-4">
    {{-- Cabeçalho --}}
    <div class="card !p-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-display text-base font-bold">Pessoas para conhecer</h3>
                <p class="text-xs text-slate-500">Perfis que combinam com você</p>
            </div>
            <x-icon name="sparkles" class="h-4 w-4 text-brand-500"/>
        </div>

        @if ($suggestions->isEmpty())
            <div class="mt-4 rounded-xl border border-dashed border-slate-200 p-4 text-center text-xs text-slate-500 dark:border-slate-700">
                Você já segue todo mundo por aqui 🎉<br>
                Novas contas aparecerão aqui em breve.
            </div>
        @else
            <ul class="mt-4 space-y-3">
                @foreach ($suggestions as $s)
                    @php $username = $s->username ?? $s->id; @endphp
                    <li wire:key="suggest-{{ $s->id }}"
                        class="flex items-center gap-2.5">
                        {{-- Avatar clicável --}}
                        <a href="{{ url('/u/' . $username) }}" class="shrink-0" wire:navigate>
                            <x-avatar :user="$s" size="sm"/>
                        </a>

                        {{-- Nome + headline clicáveis --}}
                        <div class="min-w-0 flex-1">
                            <a href="{{ url('/u/' . $username) }}"
                               wire:navigate
                               class="block">
                                <p class="truncate text-sm font-semibold leading-tight hover:underline">
                                    {{ $s->name }}
                                </p>
                                <p class="truncate text-xs text-slate-500">
                                    {{ $s->headline ?: '@' . $username }}
                                </p>
                            </a>
                        </div>

                        {{-- Botão Seguir --}}
                        <button type="button"
                                wire:click="follow({{ $s->id }})"
                                wire:loading.attr="disabled"
                                wire:target="follow({{ $s->id }})"
                                class="shrink-0 rounded-full bg-slate-900 px-3 py-1 text-[11px] font-semibold text-white transition hover:bg-brand-500 dark:bg-white dark:text-slate-900 dark:hover:bg-brand-500 dark:hover:text-white"
                                title="Seguir {{ $s->name }}">
                            <span wire:loading.remove wire:target="follow({{ $s->id }})">+ Seguir</span>
                            <span wire:loading wire:target="follow({{ $s->id }})">...</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Card resumo do usuário logado (motivacional) --}}
    @auth
        @php
            // Se for empresa, mostramos o nome fantasia/razão social ao invés
            // do nome do responsável de contato (auth()->user()->name).
            $__sbUser = auth()->user();
            $__sbDisplayName = $__sbUser->name;
            if (($__sbUser->type ?? '') === 'company' && ($__cp = $__sbUser->companyProfile)) {
                $__sbDisplayName = $__cp->trade_name ?: ($__cp->legal_name ?: $__sbDisplayName);
            }
        @endphp
        <div class="card !p-4">
            <div class="flex items-center gap-3">
                <x-avatar :user="auth()->user()" size="md"/>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold">{{ $__sbDisplayName }}</p>
                    <p class="text-xs text-slate-500">
                        Seguindo <strong class="text-ink dark:text-white">{{ $followingCount }}</strong>
                    </p>
                </div>
            </div>
            @if ($followingCount === 0)
                <p class="mt-3 flex items-start gap-2 rounded-xl bg-brand-50 p-3 text-xs text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                    {{-- Ícone: lâmpada (dica) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-1m-3 4h6m-9-8a6 6 0 1 1 9.6 4.8 3 3 0 0 0-1.2 2.4V17H9v-.8a3 3 0 0 0-1.2-2.4A6 6 0 0 1 6 13Z"/>
                    </svg>
                    <span>Siga algumas pessoas para deixar seu feed mais interessante!</span>
                </p>
            @endif
        </div>
    @endauth
</div>
