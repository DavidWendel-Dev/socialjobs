<div class="space-y-4">
    <h1 class="font-display text-2xl font-bold">Meus cursos</h1>

    <div class="card !p-2">
        <div class="flex gap-1">
            @foreach (['ongoing' => 'Em andamento', 'done' => 'Concluídos'] as $key => $label)
                <button wire:click="setTab('{{ $key }}')"
                        class="flex-1 rounded-xl px-4 py-2 text-sm font-medium {{ $tab === $key ? 'bg-brand-500 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @php
            $filtered = $enrollments->filter(fn ($e) => $tab === 'done' ? ($e->completed_at ?? null) : ! ($e->completed_at ?? null));
        @endphp
        @forelse ($filtered as $enr)
            <div class="card">
                <h3 class="font-semibold">{{ $enr->course->title ?? 'Curso' }}</h3>
                <div class="mt-2 h-2 rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-2 rounded-full bg-brand-500" style="width: {{ (int) ($enr->progress ?? 0) }}%"></div>
                </div>
                <p class="mt-2 text-xs text-slate-500">{{ (int) ($enr->progress ?? 0) }}% concluído</p>
            </div>
        @empty
            <div class="col-span-full card text-center text-slate-500">Nenhum curso {{ $tab === 'done' ? 'concluído' : 'em andamento' }}.</div>
        @endforelse
    </div>
</div>
