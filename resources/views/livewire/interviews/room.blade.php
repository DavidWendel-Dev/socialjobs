<div class="grid grid-cols-12 gap-4">
    {{-- Avatar do entrevistador --}}
    <aside class="col-span-12 lg:col-span-3">
        <div class="card sticky top-24 text-center">
            <div class="mx-auto grid h-32 w-32 place-items-center rounded-full bg-gradient-to-br from-brand-500 to-accent-500 text-4xl font-display font-bold text-white">
                IA
            </div>
            <h3 class="mt-3 font-display font-bold">Entrevistador AI</h3>
            <p class="text-xs text-slate-500">Simulação em tempo real</p>

            @if (! $finished)
                <button wire:click="finish" class="btn-secondary mt-4 w-full">Encerrar entrevista</button>
            @endif
        </div>
    </aside>

    {{-- Chat --}}
    <div class="col-span-12 lg:col-span-9 space-y-3">
        @if ($finished && $report)
            <div class="card">
                <h2 class="font-display text-xl font-bold">Relatório da entrevista</h2>
                <p class="mt-2 text-3xl font-bold text-brand-600">{{ $report['score'] ?? 0 }} / 100</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <h4 class="font-semibold text-emerald-700">Pontos fortes</h4>
                        <ul class="mt-1 list-disc pl-4 text-sm text-slate-600">
                            @foreach (($report['strengths'] ?? []) as $s) <li>{{ $s }}</li> @endforeach
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-orange-700">A melhorar</h4>
                        <ul class="mt-1 list-disc pl-4 text-sm text-slate-600">
                            @foreach (($report['improvements'] ?? []) as $s) <li>{{ $s }}</li> @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="card !p-4 min-h-[400px] space-y-3">
            @foreach ($turns as $t)
                <div class="flex {{ $t['role'] === 'user' ? 'justify-end' : '' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2 text-sm
                                {{ $t['role'] === 'user' ? 'bg-brand-500 text-white' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100' }}">
                        {{ $t['text'] }}
                    </div>
                </div>
            @endforeach
        </div>

        @if (! $finished)
            <form wire:submit.prevent="send" class="flex gap-2">
                <button type="button" class="btn-ghost !p-3"><x-icon name="mic" class="w-5 h-5"/></button>
                <input type="text" wire:model="message" class="input" placeholder="Escreva sua resposta...">
                <button type="submit" class="btn-primary"><x-icon name="arrow-right" class="w-4 h-4"/></button>
            </form>
        @endif
    </div>
</div>
