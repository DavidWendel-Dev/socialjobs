<div class="mx-auto max-w-3xl card space-y-6">

    {{-- Progresso do wizard --}}
    <div class="flex items-center gap-2">
        @for ($i = 1; $i <= 3; $i++)
            <div class="h-2 flex-1 rounded-full {{ $i <= $step ? 'bg-brand-500' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
        @endfor
    </div>

    <div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">
            Passo {{ $step }} de 3
        </p>
        <h1 class="mt-1 font-display text-2xl font-bold text-slate-900 dark:text-white">
            @switch($step)
                @case(1) Informações básicas @break
                @case(2) Descrição da vaga @break
                @case(3) Salário e publicação @break
            @endswitch
        </h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            @switch($step)
                @case(1) Comece com o essencial. Você pode editar depois. @break
                @case(2) Descreva responsabilidades, requisitos e diferenciais. Use a IA se quiser. @break
                @case(3) A faixa salarial é opcional, mas aumenta o interesse dos candidatos. @break
            @endswitch
        </p>
    </div>

    @if ($flashError)
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
            {{ $flashError }}
        </div>
    @endif

    {{-- ============================================================
         Passo 1: Informações básicas
         ============================================================ --}}
    @if ($step === 1)
        <div class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                    Título da vaga <span class="text-rose-500">*</span>
                </label>
                <input type="text" wire:model="title" class="input"
                       placeholder="Ex: Desenvolvedor(a) Full-Stack Pleno">
                @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Senioridade <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model="seniority" class="input">
                        <option value="junior">Júnior</option>
                        <option value="mid">Pleno</option>
                        <option value="senior">Sênior</option>
                        <option value="lead">Lead / Especialista</option>
                    </select>
                    @error('seniority') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Modalidade <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model="modality" class="input">
                        <option value="remote">Remoto</option>
                        <option value="hybrid">Híbrido</option>
                        <option value="onsite">Presencial</option>
                    </select>
                    @error('modality') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Tipo de contrato <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model="contractType" class="input">
                        <option value="clt">CLT</option>
                        <option value="pj">PJ</option>
                        <option value="freelance">Freelancer</option>
                        <option value="internship">Estágio</option>
                    </select>
                    @error('contractType') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Localização
                    </label>
                    <input type="text" wire:model="location" class="input" placeholder="Ex: São Paulo, SP">
                    <p class="mt-1 text-[10px] text-slate-400">Deixe em branco se for 100% remoto.</p>
                    @error('location') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================
         Passo 2: Descrição
         ============================================================ --}}
    @if ($step === 2)
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    Descrição da vaga <span class="text-rose-500">*</span>
                </label>
                <button type="button" wire:click="improveWithAi" wire:loading.attr="disabled" wire:target="improveWithAi"
                        class="btn-ghost text-xs">
                    <x-icon name="sparkles" class="h-4 w-4"/>
                    <span wire:loading.remove wire:target="improveWithAi">Melhorar com IA</span>
                    <span wire:loading wire:target="improveWithAi">Melhorando...</span>
                </button>
            </div>
            <textarea wire:model="description" rows="12" class="input"
                      placeholder="Sobre a vaga, responsabilidades, requisitos, diferenciais..."></textarea>
            <p class="text-[10px] text-slate-400">Mínimo 20 caracteres. Dica: use bullets (- ) para listas.</p>
            @error('description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
    @endif

    {{-- ============================================================
         Passo 3: Salário
         ============================================================ --}}
    @if ($step === 3)
        <div class="space-y-4">
            <div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Faixa salarial (opcional)</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Vagas com salário divulgado recebem até 3× mais candidaturas qualificadas.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Salário mínimo (R$)
                    </label>
                    <input type="number" step="0.01" min="0" wire:model="salaryMin" class="input" placeholder="Ex: 5000">
                    @error('salaryMin') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Salário máximo (R$)
                    </label>
                    <input type="number" step="0.01" min="0" wire:model="salaryMax" class="input" placeholder="Ex: 8000">
                    @error('salaryMax') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Preview compacto --}}
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Prévia</p>
                <p class="mt-1 font-display text-lg font-bold text-slate-900 dark:text-white">{{ $title ?: '—' }}</p>
                <div class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400">
                    <span class="chip">
                        @switch($seniority)
                            @case('junior') Júnior @break
                            @case('mid') Pleno @break
                            @case('senior') Sênior @break
                            @case('lead') Lead @break
                        @endswitch
                    </span>
                    <span class="chip">
                        @switch($modality)
                            @case('remote') Remoto @break
                            @case('hybrid') Híbrido @break
                            @case('onsite') Presencial @break
                        @endswitch
                    </span>
                    <span class="chip">{{ strtoupper($contractType) }}</span>
                    @if ($location) <span class="chip">📍 {{ $location }}</span> @endif
                    @if ($salaryMin !== null || $salaryMax !== null)
                        <span class="chip">💰
                            R$ {{ number_format((float) ($salaryMin ?? 0), 0, ',', '.') }}
                            @if ($salaryMax !== null) — R$ {{ number_format((float) $salaryMax, 0, ',', '.') }} @endif
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================
         Navegação
         ============================================================ --}}
    <div class="flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-800">
        <button type="button" wire:click="back" class="btn-ghost" @if($step === 1) disabled @endif>
            ← Voltar
        </button>
        @if ($step < 3)
            <button type="button" wire:click="next" class="btn-primary">
                Continuar →
            </button>
        @else
            <button type="button" wire:click="publish" wire:loading.attr="disabled" wire:target="publish" class="btn-primary">
                <span wire:loading.remove wire:target="publish">✨ Publicar vaga</span>
                <span wire:loading wire:target="publish">Publicando...</span>
            </button>
        @endif
    </div>
</div>
