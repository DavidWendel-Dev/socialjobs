<div class="space-y-4">
    {{-- ============================================================
         Header com progresso de XP e nível atual
         ============================================================ --}}
    <div class="card">
        <div class="flex flex-col items-center gap-5 sm:flex-row sm:items-center">
            {{-- Círculo de progresso --}}
            @php
                $pct = (float) ($level['progress_to_next'] ?? 0) * 100;
                $dashArray = round($pct * 100.53 / 100, 2);
            @endphp
            <div class="relative h-24 w-24 shrink-0">
                <svg viewBox="0 0 36 36" class="h-24 w-24 -rotate-90">
                    <circle cx="18" cy="18" r="16" fill="none" stroke="currentColor" stroke-width="3"
                            class="text-slate-200 dark:text-slate-700"/>
                    <circle cx="18" cy="18" r="16" fill="none" stroke-width="3"
                            stroke-dasharray="{{ $dashArray }} 100.53"
                            stroke="{{ $level['ring_color'] ?? '#22C55E' }}"/>
                </svg>
                <span class="absolute inset-0 grid place-items-center font-display text-lg font-bold">
                    Nv.{{ $level['level'] ?? 1 }}
                </span>
            </div>

            <div class="min-w-0 flex-1 text-center sm:text-left">
                <p class="text-xs uppercase tracking-widest text-slate-500">Nível atual</p>
                <h1 class="font-display text-2xl font-bold"
                    style="color: {{ $level['ring_color'] ?? '#22C55E' }}">
                    {{ $level['name'] ?? 'Explorador' }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    <strong class="text-ink dark:text-white">{{ number_format($totalXp, 0, ',', '.') }} XP</strong>
                    @if (($level['progress_to_next'] ?? 0) < 1)
                        &nbsp;·&nbsp; faltam
                        <strong class="text-ink dark:text-white">
                            {{ number_format(max(0, (int) ($level['next_at'] ?? 0) - $totalXp), 0, ',', '.') }}
                        </strong>
                        XP para o próximo nível
                    @else
                        <span class="font-medium text-brand-600">— nível máximo atingido!</span>
                    @endif
                </p>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-2 rounded-full transition-all"
                         style="width: {{ min(100, $pct) }}%; background-color: {{ $level['ring_color'] ?? '#22C55E' }}"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Abas --}}
    <div class="card !p-2">
        <div class="flex gap-1">
            @foreach (['history' => 'Histórico', 'badges' => 'Badges', 'ranking' => 'Ranking'] as $key => $label)
                <button wire:click="setTab('{{ $key }}')"
                        class="flex-1 rounded-xl px-4 py-2 text-sm font-medium transition
                               {{ $tab === $key
                                   ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900'
                                   : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Conteúdo --}}
    <div class="card">
        @if ($tab === 'history')
            <h3 class="font-display text-lg font-bold">Últimos ganhos</h3>
            <ul class="mt-3 divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($events as $ev)
                    <li class="flex items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">
                                {{ $actionLabels[$ev->action] ?? $ev->action }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ optional($ev->created_at)->diffForHumans() }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-brand-500/10 px-2.5 py-0.5 text-xs font-bold text-brand-700 dark:text-brand-300">
                            {{ $ev->xp >= 0 ? '+' : '' }}{{ $ev->xp }} XP
                        </span>
                    </li>
                @empty
                    <li class="py-8 text-center text-sm text-slate-500">
                        Sem eventos ainda. Publique um post, curta alguém ou faça uma candidatura para começar a ganhar XP.
                    </li>
                @endforelse
            </ul>
        @elseif ($tab === 'badges')
            <h3 class="font-display text-lg font-bold">Badges</h3>
            @if ($badges->count())
                <div class="mt-4 grid grid-cols-3 gap-3 sm:grid-cols-4">
                    @foreach ($badges as $b)
                        @php $earned = in_array($b->id, $userBadgeIds, true); @endphp
                        <div class="rounded-2xl p-4 text-center transition
                                    {{ $earned
                                        ? 'bg-gradient-to-br from-brand-50 to-accent/10 ring-2 ring-brand-500/30 dark:from-brand-500/10 dark:to-accent/10'
                                        : 'bg-slate-50 dark:bg-slate-800 opacity-60' }}"
                             title="{{ $b->description }}">
                            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full text-xl text-white
                                        {{ $earned
                                            ? 'bg-gradient-to-br from-brand-500 to-accent'
                                            : 'bg-slate-300 dark:bg-slate-700' }}">
                                {{ mb_substr($b->name ?? '?', 0, 1) }}
                            </div>
                            <p class="mt-2 text-xs font-semibold">{{ $b->name }}</p>
                            @if (! $earned)
                                <p class="text-[10px] text-slate-500">bloqueada</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-3 text-sm text-slate-500">Nenhuma badge configurada.</p>
            @endif
        @else
            <h3 class="font-display text-lg font-bold">Ranking</h3>
            <p class="mt-2 text-sm text-slate-500">Veja quem está no topo da comunidade.</p>
            <a href="{{ route('leaderboard') }}" class="btn-primary mt-4">Ver ranking completo</a>
        @endif
    </div>
</div>
