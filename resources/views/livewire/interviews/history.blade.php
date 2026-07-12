<div class="space-y-4">
    <h1 class="font-display text-2xl font-bold">Histórico de entrevistas</h1>

    <div class="card !p-0">
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($sessions as $s)
                <li class="flex items-center gap-4 p-4">
                    <div class="grid h-10 w-10 place-items-center rounded-2xl bg-brand-100 text-brand-700 font-display font-bold">
                        <x-icon name="mic" class="w-5 h-5"/>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium">{{ $s->role ?? 'Simulação' }} · {{ $s->seniority ?? '' }}</p>
                        <p class="truncate text-xs text-slate-500">{{ optional($s->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    <x-chip color="brand">{{ $s->score ?? '—' }} pts</x-chip>
                </li>
            @empty
                <li class="p-8 text-center text-slate-500">Você ainda não fez nenhuma simulação.</li>
            @endforelse
        </ul>
    </div>
</div>
