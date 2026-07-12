<div class="mx-auto max-w-5xl space-y-5">
    {{-- Header --}}
    <div class="rounded-2xl bg-gradient-to-br from-brand-500 via-brand-600 to-accent p-6 text-white">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider opacity-90">
                    <x-icon name="academic" class="h-3.5 w-3.5"/>
                    {{ $courseId ? 'Editar curso' : 'Novo curso' }}
                </div>
                <h1 class="mt-1 font-display text-2xl font-bold">
                    {{ $courseId ? $title : 'Criar curso interno' }}
                </h1>
                <p class="mt-1 text-xs opacity-90">Configure informações gerais, adicione módulos e aulas, publique.</p>
            </div>
            <a href="{{ route('company.courses.index') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-3 py-2 text-xs font-semibold text-white hover:bg-white/30">
                <x-icon name="arrow-left" class="h-4 w-4"/>
                Voltar
            </a>
        </div>
    </div>

    {{-- Abas --}}
    <div class="flex flex-wrap gap-2 border-b border-slate-100 pb-2 dark:border-slate-800">
        @foreach ([
            'info'    => ['Info geral', 'sparkles'],
            'modules' => ['Módulos & Aulas', 'academic'],
            'publish' => ['Publicação', 'check'],
        ] as $key => $meta)
            <button type="button"
                    wire:click="setTab('{{ $key }}')"
                    class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-semibold transition
                        {{ $tab === $key
                            ? 'bg-brand-500 text-white shadow-soft'
                            : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700' }}">
                <x-icon :name="$meta[1]" class="h-3.5 w-3.5"/>
                {{ $meta[0] }}
            </button>
        @endforeach
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- ===================== INFO GERAL ===================== --}}
        <div class="card space-y-4" @if($tab !== 'info') style="display:none" @endif>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Título do curso *</label>
                    <input type="text" wire:model="title" class="input" placeholder="Ex.: Onboarding — Cultura da Empresa"/>
                    @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Categoria</label>
                    <input type="text" wire:model="category" class="input" placeholder="Onboarding, Segurança, Vendas..."/>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Nível *</label>
                    <select wire:model="level" class="input">
                        <option value="beginner">Iniciante</option>
                        <option value="intermediate">Intermediário</option>
                        <option value="advanced">Avançado</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Duração estimada (horas) *</label>
                    <input type="number" min="1" max="500" wire:model="duration_hours" class="input"/>
                    @error('duration_hours') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Vaga vinculada (opcional)</label>
                    <select wire:model="job_listing_id" class="input">
                        <option value="">— Nenhuma —</option>
                        @foreach ($jobs as $j)
                            <option value="{{ $j->id }}">{{ $j->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">URL da thumbnail (opcional)</label>
                    <input type="url" wire:model="thumbnail_url" class="input" placeholder="https://..."/>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Descrição curta</label>
                    <textarea wire:model="short_description" rows="2" class="input" placeholder="Um resumo em 1-2 linhas..."></textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Descrição completa</label>
                    <textarea wire:model="description" rows="5" class="input" placeholder="Objetivos do curso, público-alvo, o que o aluno aprenderá..."></textarea>
                </div>
            </div>
        </div>

        {{-- ===================== MÓDULOS & AULAS ===================== --}}
        <div class="space-y-4" @if($tab !== 'modules') style="display:none" @endif>
            @if (empty($modules))
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-center dark:border-slate-700 dark:bg-slate-900">
                    <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                        <x-icon name="academic" class="h-6 w-6"/>
                    </div>
                    <p class="font-semibold">Nenhum módulo ainda</p>
                    <p class="mt-1 text-sm text-slate-500">Organize seu curso em módulos e aulas.</p>
                </div>
            @endif

            @foreach ($modules as $mIdx => $module)
                <div wire:key="module-{{ $mIdx }}" class="card space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="grid h-8 w-8 place-items-center rounded-xl bg-brand-50 text-brand-700 font-bold text-sm dark:bg-brand-500/10 dark:text-brand-300">
                            {{ $mIdx + 1 }}
                        </span>
                        <input type="text"
                               wire:model="modules.{{ $mIdx }}.title"
                               class="input flex-1"
                               placeholder="Título do módulo"/>
                        <button type="button"
                                wire:click="removeModule({{ $mIdx }})"
                                wire:confirm="Remover este módulo e suas aulas?"
                                class="grid h-9 w-9 place-items-center rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:hover:bg-rose-500/10"
                                title="Remover módulo">
                            <x-icon name="x" class="h-4 w-4"/>
                        </button>
                    </div>

                    <div class="space-y-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                        @foreach (($module['lessons'] ?? []) as $lIdx => $lesson)
                            <div wire:key="lesson-{{ $mIdx }}-{{ $lIdx }}"
                                 class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                                <div class="flex items-start gap-2">
                                    <span class="mt-2 text-xs font-bold text-slate-500">Aula {{ $lIdx + 1 }}</span>
                                    <div class="flex-1 space-y-2">
                                        <input type="text"
                                               wire:model="modules.{{ $mIdx }}.lessons.{{ $lIdx }}.title"
                                               class="input"
                                               placeholder="Título da aula"/>
                                        <input type="url"
                                               wire:model="modules.{{ $mIdx }}.lessons.{{ $lIdx }}.video_url"
                                               class="input"
                                               placeholder="URL do vídeo (YouTube/Vimeo)"/>
                                        <textarea wire:model="modules.{{ $mIdx }}.lessons.{{ $lIdx }}.content"
                                                  rows="3"
                                                  class="input"
                                                  placeholder="Conteúdo/anotações da aula (markdown permitido)"></textarea>
                                    </div>
                                    <button type="button"
                                            wire:click="removeLesson({{ $mIdx }}, {{ $lIdx }})"
                                            class="grid h-8 w-8 place-items-center rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10"
                                            title="Remover aula">
                                        <x-icon name="x" class="h-4 w-4"/>
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        <button type="button"
                                wire:click="addLesson({{ $mIdx }})"
                                class="w-full rounded-xl border border-dashed border-slate-300 py-2 text-xs font-semibold text-slate-600 hover:border-brand-400 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-brand-500/10">
                            + Adicionar aula
                        </button>
                    </div>
                </div>
            @endforeach

            <button type="button"
                    wire:click="addModule"
                    class="w-full rounded-2xl border border-dashed border-brand-300 bg-brand-50/30 py-3 text-sm font-bold text-brand-700 hover:bg-brand-50 dark:border-brand-500/30 dark:bg-brand-500/5 dark:text-brand-300 dark:hover:bg-brand-500/10">
                + Adicionar módulo
            </button>
        </div>

        {{-- ===================== PUBLICAÇÃO ===================== --}}
        <div class="card space-y-4" @if($tab !== 'publish') style="display:none" @endif>
            <div>
                <label class="mb-2 block text-xs font-semibold text-slate-700 dark:text-slate-300">Visibilidade *</label>
                <div class="grid gap-2 sm:grid-cols-2">
                    <label class="flex cursor-pointer gap-3 rounded-xl border p-3 transition
                        {{ $visibility === 'invite_only' ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-slate-200 dark:border-slate-700' }}">
                        <input type="radio" wire:model="visibility" value="invite_only" class="mt-1"/>
                        <div>
                            <p class="text-sm font-bold">Só por convite</p>
                            <p class="text-xs text-slate-500">Não aparece no catálogo público. Distribua o link de convite.</p>
                        </div>
                    </label>
                    <label class="flex cursor-pointer gap-3 rounded-xl border p-3 transition
                        {{ $visibility === 'public' ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-slate-200 dark:border-slate-700' }}">
                        <input type="radio" wire:model="visibility" value="public" class="mt-1"/>
                        <div>
                            <p class="text-sm font-bold">Público</p>
                            <p class="text-xs text-slate-500">Aparece no catálogo geral para qualquer candidato.</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Ações rodapé --}}
        <div class="sticky bottom-4 z-10 flex flex-wrap items-center justify-end gap-2 rounded-2xl bg-white/95 p-3 shadow-soft-lg ring-1 ring-slate-100 backdrop-blur dark:bg-slate-900/95 dark:ring-slate-800">
            <a href="{{ route('company.courses.index') }}"
               class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                Cancelar
            </a>
            <button type="submit"
                    class="btn-primary inline-flex items-center gap-2">
                <x-icon name="check" class="h-4 w-4"/>
                {{ $courseId ? 'Salvar alterações' : 'Criar curso' }}
            </button>
        </div>
    </form>
</div>
