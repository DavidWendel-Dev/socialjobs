<div class="mx-auto max-w-5xl space-y-4 pb-32">
    {{-- ============================================================
         Header
         ============================================================ --}}
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('company.assessments.index') }}"
           class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 shadow-soft ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
            ← Voltar
        </a>
        <h1 class="font-display text-xl font-bold sm:text-2xl">
            {{ $assessmentId ? 'Editar teste' : 'Novo teste customizado' }}
        </h1>
    </div>

    @if (session('error'))
        <div class="rounded-2xl bg-rose-50 p-3 text-sm text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">
            {{ session('error') }}
        </div>
    @endif

    {{-- ============================================================
         Metadados
         ============================================================ --}}
    <div class="rounded-2xl bg-white p-4 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 sm:p-6">
        <p class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500">Informações básicas</p>

        <div class="grid gap-3 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Título *</label>
                <input type="text" wire:model.blur="title" class="input" placeholder="Ex.: Vendas B2B para SaaS enterprise">
                @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Categoria *</label>
                <input type="text" wire:model.blur="category" class="input" placeholder="Ex.: Vendas, TI, Marketing">
                @error('category') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Dificuldade *</label>
                <select wire:model="difficulty" class="input">
                    <option value="basic">Básico</option>
                    <option value="intermediate">Intermediário</option>
                    <option value="advanced">Avançado</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Descrição curta *</label>
                <input type="text" wire:model.blur="short_description" class="input" placeholder="Uma linha resumindo o teste (aparece no card)">
                @error('short_description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Descrição longa</label>
                <textarea wire:model.blur="description" rows="3" class="input"
                          placeholder="Contexto completo do teste (opcional)"></textarea>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Duração (min)</label>
                <input type="number" min="5" max="180" wire:model.blur="duration_minutes" class="input">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Nota mínima (%)</label>
                <input type="number" min="0" max="100" wire:model.blur="passing_score" class="input">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Visibilidade</label>
                <select wire:model="visibility" class="input">
                    <option value="invite_only">Só por convite (recomendado)</option>
                    <option value="public">Público no catálogo</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300">Vinculado a uma vaga? (opcional)</label>
                <select wire:model="job_listing_id" class="input">
                    <option value="">— Nenhuma —</option>
                    @foreach ($jobs as $job)
                        <option value="{{ $job->id }}">{{ $job->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ============================================================
         Foco / IA
         ============================================================ --}}
    <div class="rounded-2xl bg-gradient-to-br from-brand-50 to-white p-4 shadow-soft ring-1 ring-brand-100 dark:from-brand-500/10 dark:to-slate-900 dark:ring-brand-500/20 sm:p-6">
        <p class="mb-1 flex items-center gap-2 text-sm font-bold text-brand-700 dark:text-brand-300">
            <x-icon name="sparkles" class="h-4 w-4"/>
            Gerar questões com IA
        </p>
        <p class="mb-3 text-xs text-slate-600 dark:text-slate-400">
            Descreva o foco desta empresa (produto, indústria, perfil desejado). A IA usa isso para criar questões ancoradas no seu contexto real.
        </p>
        <textarea wire:model.blur="focus_topic" rows="2" class="input mb-3"
                  placeholder="Ex.: Vendas B2B para SaaS enterprise, foco em cold outreach e discovery de dor"></textarea>

        <button type="button"
                wire:click="generateWithAi"
                wire:loading.attr="disabled"
                wire:target="generateWithAi"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-bold text-white shadow-soft hover:bg-brand-600 disabled:opacity-50">
            <span wire:loading.remove wire:target="generateWithAi" class="inline-flex items-center gap-2">
                <x-icon name="sparkles" class="h-4 w-4"/>
                Gerar 10 questões com IA
            </span>
            <span wire:loading wire:target="generateWithAi" class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                    <path d="M22 12a10 10 0 0 1-10 10"/>
                </svg>
                Gerando... isso leva 10-30s
            </span>
        </button>
    </div>

    {{-- ============================================================
         Lista de questões editáveis
         ============================================================ --}}
    <div class="rounded-2xl bg-white p-4 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 sm:p-6">
        <div class="mb-3 flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Questões ({{ count($questions) }})
            </p>
            <button type="button" wire:click="addQuestion"
                    class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                + Adicionar manual
            </button>
        </div>

        @error('questions')
            <div class="mb-3 rounded-xl bg-rose-50 p-3 text-xs text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">
                {{ $message }}
            </div>
        @enderror

        @if (empty($questions))
            <div class="rounded-xl border border-dashed border-slate-200 p-6 text-center text-xs text-slate-500 dark:border-slate-700">
                Nenhuma questão ainda. Gere com IA acima ou adicione manualmente.
            </div>
        @else
            <div class="space-y-3">
                @foreach ($questions as $i => $q)
                    <div wire:key="q-{{ $i }}"
                         class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-brand-100 px-2.5 py-0.5 text-[11px] font-bold text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                                Questão {{ $i + 1 }}
                            </span>
                            <button type="button"
                                    wire:click="removeQuestion({{ $i }})"
                                    class="text-[11px] font-semibold text-rose-600 hover:underline">
                                Remover
                            </button>
                        </div>

                        <textarea wire:model.blur="questions.{{ $i }}.statement"
                                  rows="3"
                                  class="input mb-2 text-sm"
                                  placeholder="Enunciado da questão (cenário + pergunta)"></textarea>

                        <div class="space-y-1.5">
                            @for ($opt = 0; $opt < 4; $opt++)
                                <label class="flex items-start gap-2 rounded-lg border border-slate-100 p-2 dark:border-slate-800">
                                    <input type="radio"
                                           wire:model="questions.{{ $i }}.correct_index"
                                           value="{{ $opt }}"
                                           class="mt-1 h-4 w-4 shrink-0 text-brand-500">
                                    <div class="flex-1">
                                        <div class="mb-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                            Opção {{ chr(65 + $opt) }}
                                            @if (($q['correct_index'] ?? 0) === $opt)
                                                <span class="ml-1 text-brand-600">✓ correta</span>
                                            @endif
                                        </div>
                                        <input type="text"
                                               wire:model.blur="questions.{{ $i }}.options.{{ $opt }}"
                                               class="input text-xs sm:text-sm"
                                               placeholder="Alternativa {{ chr(65 + $opt) }}">
                                    </div>
                                </label>
                            @endfor
                        </div>

                        <textarea wire:model.blur="questions.{{ $i }}.explanation"
                                  rows="2"
                                  class="input mt-2 text-xs"
                                  placeholder="Explicação (opcional) — aparece após responder"></textarea>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ============================================================
         Rodapé sticky (salvar)
         ============================================================ --}}
    <div class="fixed inset-x-0 bottom-14 z-30 border-t border-slate-100 bg-white/95 px-3 py-3 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 lg:bottom-0">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-3">
            <p class="text-[11px] text-slate-500">
                {{ count($questions) }} questões · mínimo de 5 para salvar
            </p>
            <button type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-bold text-white shadow-soft hover:bg-brand-600 disabled:opacity-50">
                <span wire:loading.remove wire:target="save">
                    {{ $assessmentId ? 'Salvar alterações' : 'Publicar teste' }}
                </span>
                <span wire:loading wire:target="save">Salvando...</span>
            </button>
        </div>
    </div>
</div>
