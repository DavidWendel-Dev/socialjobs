<div class="card !p-4">
    <div class="mb-3 flex items-center justify-between">
        <div>
            <h3 class="font-display text-base font-bold">Top da comunidade</h3>
            <p class="text-xs text-slate-500">Ranking por XP</p>
        </div>
        <x-icon name="trophy" class="h-4 w-4 text-brand-500"/>
    </div>

    @if ($top->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-xs text-slate-500 dark:border-slate-700">
            Ranking em construção 🏗️
        </div>
    @else
        <ul class="space-y-2">
            @foreach ($top as $index => $stat)
                @php
                    $username = $stat->user?->username ?? $stat->user?->id;
                    $rank     = $index + 1;
                    $medals   = ['🥇', '🥈', '🥉'];
                @endphp
                <li>
                    <a href="{{ $username ? url('/u/' . $username) : '#' }}"
                       wire:navigate
                       class="flex items-center gap-2.5 rounded-xl p-2 transition hover:bg-slate-50 dark:hover:bg-slate-800">
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            {{ $medals[$index] ?? "#{$rank}" }}
                        </span>
                        <x-avatar :user="$stat->user" size="sm"/>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold leading-tight">
                                {{ $stat->user?->name ?? 'Usuário' }}
                            </p>
                            <p class="text-[10px] text-slate-500">
                                <strong class="text-brand-600">{{ number_format($stat->total_xp, 0, ',', '.') }}</strong> XP
                            </p>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        <a href="{{ route('leaderboard') }}"
           class="mt-3 block rounded-xl bg-slate-50 py-2 text-center text-xs font-medium text-brand-600 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700">
            Ver ranking completo →
        </a>
    @endif
</div>
