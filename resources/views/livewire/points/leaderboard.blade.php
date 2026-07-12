<div class="space-y-4">
    <h1 class="font-display text-2xl font-bold">Ranking</h1>

    <div class="card !p-2">
        <div class="flex gap-1">
            @foreach (['global' => 'Global', 'week' => 'Semana', 'followers' => 'Meus seguidores'] as $key => $label)
                <button wire:click="setScope('{{ $key }}')"
                        class="flex-1 rounded-xl px-4 py-2 text-sm font-medium {{ $scope === $key ? 'bg-brand-500 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="card !p-0">
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($users as $i => $u)
                <li class="flex items-center gap-4 p-4">
                    <span class="w-6 text-center font-mono text-sm text-slate-500">{{ $i + 1 }}</span>
                    <x-avatar :user="$u->user ?? $u" size="sm"/>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium">{{ $u->user->name ?? ($u->name ?? 'Usuário') }}</p>
                        <p class="truncate text-xs text-slate-500">Nv {{ $u->level ?? 1 }}</p>
                    </div>
                    <x-chip color="brand">{{ number_format((int) ($u->xp ?? 0), 0, ',', '.') }} XP</x-chip>
                </li>
            @empty
                <li class="p-8 text-center text-sm text-slate-500">Ranking vazio.</li>
            @endforelse
        </ul>
    </div>
</div>
