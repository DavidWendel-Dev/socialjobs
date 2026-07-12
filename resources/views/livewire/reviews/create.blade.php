<div class="mx-auto max-w-2xl space-y-4 sm:space-y-6">

    {{-- ============================================================
         HEADER — Empresa que está sendo avaliada
         ============================================================ --}}
    <div class="card">
        <div class="flex items-center gap-3">
            @if ($company->user?->avatar_path)
                <img src="{{ $company->user->avatar_url }}"
                     alt="Logo de {{ $company->legal_name }}"
                     class="h-14 w-14 rounded-xl border border-slate-200 bg-white object-cover dark:border-slate-700">
            @else
                <div class="grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-brand-100 to-brand-200 text-xl font-display font-bold text-brand-700">
                    {{ mb_substr($company->legal_name ?? 'E', 0, 1) }}
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Avaliando</p>
                <h1 class="truncate font-display text-lg sm:text-xl font-bold text-slate-900 dark:text-white">
                    {{ $company->trade_name ?: $company->legal_name }}
                </h1>
            </div>
        </div>

        <p class="mt-3 rounded-xl bg-brand-50 px-3 py-2 text-xs text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
            Sua avaliação ajuda outros candidatos a conhecerem a empresa. Seja honesto e respeitoso.
        </p>
    </div>

    @error('title') <p class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">{{ $message }}</p> @enderror
    @error('pros')  <p class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">{{ $message }}</p> @enderror
    @error('cons')  <p class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">{{ $message }}</p> @enderror

    <form wire:submit.prevent="save" class="space-y-4 sm:space-y-6">

        {{-- ============================================================
             ESTRELAS por dimensão
             ============================================================ --}}
        <div class="card space-y-5">
            <div class="flex items-center gap-2">
                <x-icon name="sparkles" class="h-5 w-5 text-amber-500"/>
                <h2 class="font-display text-lg font-bold">Sua nota</h2>
            </div>

            @php
                $dims = [
                    ['ratingOverall',       'Avaliação geral',      'Como foi a experiência global?'],
                    ['ratingProcess',       'Processo seletivo',    'Organização, tempo e clareza das etapas.'],
                    ['ratingCommunication', 'Comunicação',          'Retorno, respostas e transparência.'],
                    ['ratingCulture',       'Cultura percebida',    'Ambiente e valores demonstrados.'],
                ];
            @endphp

            @foreach ($dims as [$field, $label, $help])
                <div>
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $label }}</p>
                            <p class="text-xs text-slate-500">{{ $help }}</p>
                        </div>
                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400">
                            {{ $this->{$field} }}/5
                        </span>
                    </div>
                    <div class="mt-2 flex items-center gap-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button"
                                    wire:click="setRating('{{ $field }}', {{ $i }})"
                                    class="text-2xl sm:text-3xl transition hover:scale-110
                                           {{ $this->{$field} >= $i ? 'grayscale-0' : 'grayscale opacity-40' }}"
                                    aria-label="{{ $i }} estrelas">
                                ⭐
                            </button>
                        @endfor
                    </div>
                    @error($field) <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>

        {{-- ============================================================
             TÍTULO + PROS + CONS
             ============================================================ --}}
        <div class="card space-y-4">
            <div>
                <label for="title" class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                    Título da avaliação
                </label>
                <input id="title" type="text" wire:model.defer="title" class="input mt-1"
                       maxlength="191"
                       placeholder="Ex.: Processo transparente e equipe acolhedora">
            </div>

            <div>
                <label for="pros" class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                    O que você gostou
                </label>
                <p class="text-xs text-slate-500">Pontos positivos que quer destacar.</p>
                <textarea id="pros" wire:model.defer="pros" rows="4" class="input mt-1"
                          maxlength="2000"
                          placeholder="Ex.: Equipe atenciosa, feedback rápido, clareza sobre a vaga..."></textarea>
            </div>

            <div>
                <label for="cons" class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                    O que poderia melhorar
                </label>
                <p class="text-xs text-slate-500">Aspectos que a empresa pode evoluir.</p>
                <textarea id="cons" wire:model.defer="cons" rows="4" class="input mt-1"
                          maxlength="2000"
                          placeholder="Ex.: Processo demorou mais do que o combinado, faltou retorno em uma das etapas..."></textarea>
            </div>
        </div>

        {{-- ============================================================
             TOGGLES
             ============================================================ --}}
        <div class="card space-y-4">
            <label class="flex cursor-pointer items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                        Recomendaria essa empresa?
                    </p>
                    <p class="text-xs text-slate-500">Você indicaria essa empresa a um amigo?</p>
                </div>
                <input type="checkbox" wire:model.live="wouldRecommend"
                       class="mt-1 h-5 w-9 shrink-0 appearance-none rounded-full bg-slate-300 transition
                              checked:bg-brand-500 relative cursor-pointer
                              before:absolute before:top-0.5 before:left-0.5 before:h-4 before:w-4 before:rounded-full before:bg-white before:transition
                              checked:before:translate-x-4">
            </label>

            <label class="flex cursor-pointer items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                        Avaliar anonimamente
                    </p>
                    <p class="text-xs text-slate-500">Seu nome não aparecerá publicamente.</p>
                </div>
                <input type="checkbox" wire:model.live="isAnonymous"
                       class="mt-1 h-5 w-9 shrink-0 appearance-none rounded-full bg-slate-300 transition
                              checked:bg-brand-500 relative cursor-pointer
                              before:absolute before:top-0.5 before:left-0.5 before:h-4 before:w-4 before:rounded-full before:bg-white before:transition
                              checked:before:translate-x-4">
            </label>
        </div>

        {{-- ============================================================
             AÇÕES
             ============================================================ --}}
        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ url('/c/' . $company->slug) }}"
               class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-100 dark:ring-slate-700 dark:hover:bg-slate-700">
                Cancelar
            </a>
            <button type="submit"
                    class="btn-primary inline-flex items-center justify-center gap-1.5"
                    wire:loading.attr="disabled" wire:target="save">
                <x-icon name="sparkles" class="h-4 w-4"/>
                <span wire:loading.remove wire:target="save">Enviar avaliação</span>
                <span wire:loading wire:target="save">Enviando...</span>
            </button>
        </div>
    </form>
</div>
