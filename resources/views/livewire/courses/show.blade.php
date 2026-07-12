<div class="space-y-6">
    <div class="card overflow-hidden !p-0">
        <div class="grid gap-0 md:grid-cols-2">
            <div class="h-full bg-gradient-to-br from-brand-500 to-accent-500 p-10 text-white">
                <p class="text-xs uppercase tracking-widest opacity-80">{{ $course->category ?? 'Curso' }}</p>
                <h1 class="mt-2 font-display text-3xl font-bold">{{ $course->title ?? 'Curso' }}</h1>
                <p class="mt-3 opacity-90">{{ $course->summary ?? '' }}</p>
            </div>
            <div class="p-8">
                <p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-6">{{ $course->description ?? 'Sem descrição.' }}</p>
                <div class="mt-4 flex gap-2">
                    <x-chip color="brand">{{ $course->level ?? 'Iniciante' }}</x-chip>
                    <x-chip color="slate">{{ $course->duration ?? '2h' }}</x-chip>
                    <x-chip color="sky">Certificado</x-chip>
                </div>
                <button wire:click="enroll" class="btn-primary mt-6 w-full">Matricular-se gratuitamente</button>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="font-display text-lg font-bold">Conteúdo</h3>
        <ol class="mt-3 space-y-2 text-sm">
            @forelse (($course->modules ?? []) as $i => $m)
                <li class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800">Módulo {{ $i + 1 }}: {{ $m->title ?? '' }}</li>
            @empty
                <li class="text-slate-500">Sem módulos disponíveis.</li>
            @endforelse
        </ol>
    </div>
</div>
