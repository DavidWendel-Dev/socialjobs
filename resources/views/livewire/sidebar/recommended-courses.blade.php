<div class="card !p-4">
    <div class="mb-3 flex items-center justify-between">
        <div>
            <h3 class="font-display text-base font-bold">Cursos populares</h3>
            <p class="text-xs text-slate-500">Mais procurados na plataforma</p>
        </div>
        <x-icon name="academic" class="h-4 w-4 text-brand-500"/>
    </div>

    @if ($courses->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-xs text-slate-500 dark:border-slate-700">
            Nenhum curso disponível ainda.
        </div>
    @else
        <ul class="space-y-3">
            @foreach ($courses as $course)
                <li>
                    <a href="{{ route('courses.show', $course) }}"
                       class="group block rounded-xl border border-slate-100 p-3 transition hover:border-brand-500 hover:bg-brand-50/30 dark:border-slate-800 dark:hover:border-brand-500 dark:hover:bg-brand-500/5">
                        <p class="line-clamp-2 text-sm font-semibold leading-tight group-hover:text-brand-600">
                            {{ $course->title }}
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-[10px] text-slate-500">
                            @if ($course->level)
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 font-medium dark:bg-slate-800">
                                    {{ ucfirst($course->level) }}
                                </span>
                            @endif
                            @if ($course->enrollments_count > 0)
                                <span>{{ $course->enrollments_count }} inscritos</span>
                            @endif
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        <a href="{{ route('courses.index') }}"
           class="mt-3 block rounded-xl bg-slate-50 py-2 text-center text-xs font-medium text-brand-600 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700">
            Explorar catálogo →
        </a>
    @endif
</div>
