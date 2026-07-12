<div class="space-y-4">
    <h1 class="font-display text-2xl font-bold">Minhas candidaturas</h1>

    <div class="card !p-0">
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($apps as $a)
                <li class="flex items-center gap-4 p-4">
                    <div class="grid h-10 w-10 place-items-center rounded-2xl bg-brand-100 text-brand-700 font-display font-bold">
                        {{ mb_substr($a->job->title ?? 'V', 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium">{{ $a->job->title ?? 'Vaga' }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $a->job->company_name ?? 'Empresa' }} · candidatura em {{ optional($a->created_at)->format('d/m/Y') }}</p>
                    </div>
                    <x-chip color="sky">{{ ucfirst($a->status ?? 'received') }}</x-chip>
                </li>
            @empty
                <li class="p-8 text-center text-slate-500">Você ainda não se candidatou a nenhuma vaga.</li>
            @endforelse
        </ul>
    </div>
</div>
