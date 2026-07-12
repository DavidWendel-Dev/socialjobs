<div class="grid grid-cols-12 gap-4">
    <aside class="col-span-12 lg:col-span-3">
        <div class="card sticky top-24 !p-3">
            <h3 class="mb-2 px-2 font-display font-bold">{{ $course->title ?? 'Curso' }}</h3>
            <ul class="space-y-1 text-sm">
                @forelse (($course->lessons ?? []) as $l)
                    <li>
                        <a href="{{ route('courses.learn', [$course, $l]) }}"
                           class="flex items-center gap-2 rounded-xl px-3 py-2 {{ ($lesson && ($lesson->id ?? null) === ($l->id ?? -1)) ? 'bg-brand-50 text-brand-700 font-medium' : 'hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            @if (! empty($l->completed))
                                <x-icon name="check" class="w-4 h-4 text-brand-500"/>
                            @else
                                <span class="inline-block h-4 w-4 rounded-full border border-slate-300"></span>
                            @endif
                            {{ $l->title ?? 'Aula' }}
                        </a>
                    </li>
                @empty
                    <li class="px-2 text-xs text-slate-500">Sem aulas cadastradas.</li>
                @endforelse
            </ul>
        </div>
    </aside>

    <div class="col-span-12 lg:col-span-9 space-y-4">
        <div class="card !p-0 overflow-hidden">
            <div class="aspect-video bg-slate-900">
                @if (! empty($lesson->youtube_id))
                    <iframe class="h-full w-full" src="https://www.youtube-nocookie.com/embed/{{ $lesson->youtube_id }}" title="Player" allowfullscreen></iframe>
                @else
                    <div class="grid h-full place-items-center text-white/60">Video preview</div>
                @endif
            </div>
            <div class="flex items-center justify-between p-4">
                <h1 class="font-display text-xl font-bold">{{ $lesson->title ?? 'Aula' }}</h1>
                <button wire:click="markComplete" class="btn-primary"><x-icon name="check" class="w-4 h-4"/> Concluída</button>
            </div>
        </div>

        <div class="card !p-2">
            <div class="flex gap-1 overflow-x-auto">
                @foreach (['content' => 'Conteúdo', 'materials' => 'Materiais', 'transcript' => 'Transcrição', 'discussion' => 'Discussão', 'quiz' => 'Quiz'] as $key => $label)
                    <button wire:click="setTab('{{ $key }}')"
                            class="rounded-xl px-4 py-2 text-sm font-medium {{ $tab === $key ? 'bg-brand-500 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="card">
            @switch($tab)
                @case('content')      <p class="text-sm">{{ $lesson->content ?? 'Sem conteúdo textual.' }}</p> @break
                @case('materials')    <p class="text-sm text-slate-500">Nenhum material anexado.</p> @break
                @case('transcript')   <p class="text-sm text-slate-500">Transcrição indisponível.</p> @break
                @case('discussion')   <p class="text-sm text-slate-500">Seja o primeiro a comentar.</p> @break
                @case('quiz')         <p class="text-sm text-slate-500">Sem quiz nesta aula.</p> @break
            @endswitch
        </div>
    </div>
</div>
