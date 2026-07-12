<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-2xl font-bold">Catálogo de cursos</h1>
            <p class="text-sm text-slate-500">Aprenda no seu ritmo com trilhas curadas.</p>
        </div>
        <a href="{{ route('courses.mine') }}" class="btn-secondary">Meus cursos</a>
    </div>

    <div class="card flex flex-wrap gap-3">
        <select wire:model.live="category" class="input !w-auto">
            <option value="">Todas categorias</option>
            <option>Tecnologia</option><option>Design</option><option>Produto</option><option>Soft skills</option>
        </select>
        <select wire:model.live="level" class="input !w-auto">
            <option value="">Todos os níveis</option>
            <option>Iniciante</option><option>Intermediário</option><option>Avançado</option>
        </select>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($courses as $course)
            <a href="{{ route('courses.show', $course) }}" class="card block overflow-hidden !p-0 transition hover:shadow-soft-lg">
                <div class="h-36 bg-gradient-to-br from-brand-500 to-accent-500"></div>
                <div class="p-4">
                    <p class="text-xs uppercase tracking-widest text-slate-500">{{ $course->category ?? 'Curso' }}</p>
                    <h3 class="mt-1 font-semibold line-clamp-2">{{ $course->title ?? 'Curso' }}</h3>
                    <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $course->summary ?? '' }}</p>
                    <div class="mt-3 flex items-center gap-2">
                        <x-chip color="brand">{{ $course->level ?? 'Iniciante' }}</x-chip>
                        <x-chip color="slate">{{ $course->duration ?? '2h' }}</x-chip>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full card text-center text-slate-500">Nenhum curso disponível ainda.</div>
        @endforelse
    </div>
</div>
