<div class="mx-auto max-w-5xl space-y-5"
     x-data="{
        copy(text) {
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                this.$dispatch('toast', { msg: 'Copiado!' });
            });
        }
     }"
     @toast.window="const t = document.getElementById('ai-toast-company'); if (t) { t.textContent = $event.detail.msg; t.classList.remove('opacity-0'); setTimeout(() => t.classList.add('opacity-0'), 2500); }">

    {{-- Toast --}}
    <div id="ai-toast-company"
         class="pointer-events-none fixed left-1/2 top-6 z-[100] -translate-x-1/2 rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white opacity-0 shadow-lg transition-opacity dark:bg-white dark:text-slate-900">
    </div>

    {{-- ============================================================
         Hero
         ============================================================ --}}
    <div class="card">
        <div class="flex items-start gap-4">
            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-500/10 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                <x-icon name="sparkles" class="h-6 w-6"/>
            </div>
            <div class="min-w-0 flex-1">
                <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">
                    Assistente IA · RH
                </p>
                <h1 class="font-display text-xl font-bold text-slate-900 dark:text-white sm:text-2xl">
                    Recrute com inteligência artificial
                </h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    6 ferramentas para acelerar o ciclo de contratação: descrições de vaga, entrevistas,
                    análise de CV, e-mails, salário e insights.
                </p>
            </div>
        </div>
    </div>

    @php
        $tabs = [
            ['key' => 'job-description',     'label' => 'Descrição de vaga',   'icon' => 'briefcase',  'desc' => 'Melhore em Markdown'],
            ['key' => 'interview-questions', 'label' => 'Perguntas',           'icon' => 'mic',        'desc' => 'Entrevista sob medida'],
            ['key' => 'cv-match',            'label' => 'Match CV × Vaga',     'icon' => 'trophy',     'desc' => 'Score 0-100 + análise'],
            ['key' => 'feedback-email',      'label' => 'E-mail candidato',    'icon' => 'message',    'desc' => 'Aprovação, próxima etapa…'],
            ['key' => 'salary',              'label' => 'Salário de mercado',  'icon' => 'sparkles',   'desc' => 'Faixa + benefícios'],
            ['key' => 'insights',            'label' => 'Insights de vagas',   'icon' => 'academic',   'desc' => 'Funil, alertas'],
        ];
    @endphp

    <div class="grid gap-5 lg:grid-cols-[240px_1fr]">

        {{-- Pills mobile --}}
        <div class="lg:hidden -mx-2 overflow-x-auto px-2">
            <div class="flex gap-2 pb-1">
                @foreach ($tabs as $t)
                    @php $active = $tab === $t['key']; @endphp
                    <button type="button" wire:click="setTab('{{ $t['key'] }}')"
                            class="flex shrink-0 items-center gap-2 rounded-full border px-3 py-2 text-xs font-semibold transition
                                   {{ $active
                                       ? 'border-brand-500 bg-brand-500 text-white shadow-soft'
                                       : 'border-slate-200 bg-white text-slate-700 hover:border-brand-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200' }}">
                        <x-icon :name="$t['icon']" class="h-3.5 w-3.5"/>
                        {{ $t['label'] }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Sidebar desktop --}}
        <aside class="hidden lg:block">
            <nav class="sticky top-24 space-y-1 rounded-2xl bg-white p-2 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
                @foreach ($tabs as $t)
                    @php $active = $tab === $t['key']; @endphp
                    <button type="button" wire:click="setTab('{{ $t['key'] }}')"
                            class="group flex w-full items-start gap-3 rounded-xl p-3 text-left transition
                                   {{ $active ? 'bg-brand-500/10' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg
                                     {{ $active ? 'bg-brand-500 text-white' : 'bg-brand-500/10 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400' }}">
                            <x-icon :name="$t['icon']" class="h-4 w-4"/>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold {{ $active ? 'text-brand-700 dark:text-brand-300' : 'text-slate-800 dark:text-slate-200' }}">{{ $t['label'] }}</p>
                            <p class="truncate text-[10px] text-slate-500">{{ $t['desc'] }}</p>
                        </div>
                    </button>
                @endforeach
            </nav>
        </aside>

        {{-- Conteúdo principal --}}
        <div class="min-w-0">

            {{-- ============================================================
                 1. DESCRIÇÃO DE VAGA
                 ============================================================ --}}
            @if ($tab === 'job-description')
                <section class="card">
                    <h2 class="mb-1 font-display text-lg font-bold text-slate-900 dark:text-white">Melhorar descrição de vaga</h2>
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">A IA transforma o rascunho em uma descrição profissional em Markdown.</p>

                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Título da vaga <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="jdTitle" class="input" placeholder="Ex: Desenvolvedor Full Stack Sênior">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Rascunho atual (opcional)</label>
                            <textarea wire:model="jdCurrent" rows="4" class="input" placeholder="Cole o que já tem escrito..."></textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Skills chave (separadas por vírgula)</label>
                            <input type="text" wire:model="jdSkills" class="input" placeholder="Ex: Laravel, React, SQL">
                        </div>
                    </div>

                    <button wire:click="runJobDescription" wire:loading.attr="disabled" wire:target="runJobDescription" class="btn-primary mt-4 w-full">
                        <span wire:loading.remove wire:target="runJobDescription">✨ Gerar com IA</span>
                        <span wire:loading wire:target="runJobDescription">Redigindo...</span>
                    </button>

                    @if ($outputJobDescription !== '')
                        <div class="mt-6 space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">📄 Descrição gerada</p>
                                <div class="flex gap-2">
                                    <button type="button" @click="copy(@js($outputJobDescription))" class="btn-ghost text-xs">Copiar</button>
                                    <button type="button" wire:click="clear('job-description')" class="btn-ghost text-xs">Limpar</button>
                                </div>
                            </div>
                            <div class="prose prose-sm max-w-none rounded-2xl border border-slate-100 bg-slate-50 p-5 text-slate-800 dark:prose-invert dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-200">
                                {!! \Illuminate\Support\Str::markdown($outputJobDescription) !!}
                            </div>
                        </div>
                    @endif
                </section>
            @endif

            {{-- ============================================================
                 2. PERGUNTAS DE ENTREVISTA
                 ============================================================ --}}
            @if ($tab === 'interview-questions')
                <section class="card">
                    <h2 class="mb-1 font-display text-lg font-bold text-slate-900 dark:text-white">Gerar perguntas de entrevista</h2>
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Perguntas sob medida para a vaga escolhida.</p>

                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Vaga <span class="text-rose-500">*</span></label>
                            @if ($openJobs->count())
                                <select wire:model="iqJobId" class="input">
                                    <option value="">Selecione...</option>
                                    @foreach ($openJobs as $j)
                                        <option value="{{ $j->id }}">{{ $j->title }}</option>
                                    @endforeach
                                </select>
                            @else
                                <p class="text-xs text-slate-400">Você não tem vagas abertas no momento.</p>
                            @endif
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Nível</label>
                                <select wire:model="iqLevel" class="input">
                                    <option value="junior">Júnior</option>
                                    <option value="pleno">Pleno</option>
                                    <option value="senior">Sênior</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Quantidade (5-15)</label>
                                <input type="number" wire:model="iqCount" min="5" max="15" class="input">
                            </div>
                        </div>
                    </div>

                    <button wire:click="runInterviewQuestions" wire:loading.attr="disabled" wire:target="runInterviewQuestions" class="btn-primary mt-4 w-full">
                        <span wire:loading.remove wire:target="runInterviewQuestions">✨ Gerar perguntas</span>
                        <span wire:loading wire:target="runInterviewQuestions">Gerando...</span>
                    </button>

                    @if ($outputInterviewQuestions !== '')
                        <div class="mt-6 space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">🎤 Perguntas</p>
                                <div class="flex gap-2">
                                    <button type="button" @click="copy(@js($outputInterviewQuestions))" class="btn-ghost text-xs">Copiar</button>
                                    <button type="button" wire:click="clear('interview-questions')" class="btn-ghost text-xs">Limpar</button>
                                </div>
                            </div>
                            <div class="prose prose-sm max-w-none rounded-2xl border border-slate-100 bg-slate-50 p-5 text-slate-800 dark:prose-invert dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-200">
                                {!! \Illuminate\Support\Str::markdown($outputInterviewQuestions) !!}
                            </div>
                        </div>
                    @endif
                </section>
            @endif

            {{-- ============================================================
                 3. CV × VAGA
                 ============================================================ --}}
            @if ($tab === 'cv-match')
                <section class="card">
                    <h2 class="mb-1 font-display text-lg font-bold text-slate-900 dark:text-white">Analisar CV × vaga</h2>
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Score de compatibilidade + análise de pontos fortes, gaps e recomendação.</p>

                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Vaga <span class="text-rose-500">*</span></label>
                            @if ($openJobs->count())
                                <select wire:model.live="cmJobId" class="input">
                                    <option value="">Selecione...</option>
                                    @foreach ($openJobs as $j)
                                        <option value="{{ $j->id }}">{{ $j->title }}</option>
                                    @endforeach
                                </select>
                            @else
                                <p class="text-xs text-slate-400">Nenhuma vaga aberta.</p>
                            @endif
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Candidato (aplicantes desta vaga) <span class="text-rose-500">*</span></label>
                            @if ($cmCandidates->count())
                                <select wire:model="cmCandidateId" class="input">
                                    <option value="">Selecione...</option>
                                    @foreach ($cmCandidates as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <p class="text-xs text-slate-400">Selecione uma vaga com candidatos.</p>
                            @endif
                        </div>
                    </div>

                    <button wire:click="runCvMatch" wire:loading.attr="disabled" wire:target="runCvMatch" class="btn-primary mt-4 w-full">
                        <span wire:loading.remove wire:target="runCvMatch">✨ Analisar</span>
                        <span wire:loading wire:target="runCvMatch">Analisando...</span>
                    </button>

                    @if ($outputCvMatch !== '')
                        <div class="mt-6 space-y-3">
                            @if ($cmScore !== null)
                                <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                                    <div class="grid h-16 w-16 shrink-0 place-items-center rounded-full bg-white text-lg font-bold text-brand-600 shadow-soft dark:bg-slate-900">
                                        {{ $cmScore }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Score de compatibilidade</p>
                                        <p class="text-xs text-slate-500">Valor de 0 a 100 baseado em skills, experiência e requisitos.</p>
                                    </div>
                                </div>
                            @endif
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">📊 Análise</p>
                                <div class="flex gap-2">
                                    <button type="button" @click="copy(@js($outputCvMatch))" class="btn-ghost text-xs">Copiar</button>
                                    <button type="button" wire:click="clear('cv-match')" class="btn-ghost text-xs">Limpar</button>
                                </div>
                            </div>
                            <div class="prose prose-sm max-w-none rounded-2xl border border-slate-100 bg-slate-50 p-5 text-slate-800 dark:prose-invert dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-200">
                                {!! \Illuminate\Support\Str::markdown($outputCvMatch) !!}
                            </div>
                        </div>
                    @endif
                </section>
            @endif

            {{-- ============================================================
                 4. E-MAIL DE FEEDBACK
                 ============================================================ --}}
            @if ($tab === 'feedback-email')
                <section class="card">
                    <h2 class="mb-1 font-display text-lg font-bold text-slate-900 dark:text-white">E-mail de feedback</h2>
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Aprovação, rejeição, próxima etapa ou agendar entrevista.</p>

                    <div class="space-y-3">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Tipo</label>
                                <select wire:model="fbType" class="input">
                                    <option value="approval">Aprovação / oferta</option>
                                    <option value="next-step">Próxima etapa</option>
                                    <option value="interview">Agendar entrevista</option>
                                    <option value="rejection">Rejeição respeitosa</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Tom</label>
                                <select wire:model="fbTone" class="input">
                                    <option value="friendly">Amigável</option>
                                    <option value="formal">Formal</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Candidato <span class="text-rose-500">*</span></label>
                            @if ($fbCandidates->count())
                                <select wire:model="fbCandidateId" class="input">
                                    <option value="">Selecione...</option>
                                    @foreach ($fbCandidates as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <p class="text-xs text-slate-400">Você ainda não tem candidaturas.</p>
                            @endif
                        </div>
                    </div>

                    <button wire:click="runFeedbackEmail" wire:loading.attr="disabled" wire:target="runFeedbackEmail" class="btn-primary mt-4 w-full">
                        <span wire:loading.remove wire:target="runFeedbackEmail">✨ Redigir e-mail</span>
                        <span wire:loading wire:target="runFeedbackEmail">Escrevendo...</span>
                    </button>

                    @if ($outputFeedbackEmail !== '')
                        <div class="mt-6 space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">📧 E-mail pronto</p>
                                <div class="flex gap-2">
                                    <button type="button" @click="copy(@js($outputFeedbackEmail))" class="btn-ghost text-xs">Copiar</button>
                                    <button type="button" wire:click="clear('feedback-email')" class="btn-ghost text-xs">Limpar</button>
                                </div>
                            </div>
                            <div class="prose prose-sm max-w-none rounded-2xl border border-slate-100 bg-slate-50 p-5 text-slate-800 dark:prose-invert dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-200">
                                {!! \Illuminate\Support\Str::markdown($outputFeedbackEmail) !!}
                            </div>
                        </div>
                    @endif
                </section>
            @endif

            {{-- ============================================================
                 5. SALÁRIO
                 ============================================================ --}}
            @if ($tab === 'salary')
                <section class="card">
                    <h2 class="mb-1 font-display text-lg font-bold text-slate-900 dark:text-white">Sugerir salário de mercado</h2>
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Estimativa de faixa salarial + benefícios comuns.</p>

                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Cargo <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="slRole" class="input" placeholder="Ex: Analista de Marketing">
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Nível</label>
                                <select wire:model="slLevel" class="input">
                                    <option value="junior">Júnior</option>
                                    <option value="pleno">Pleno</option>
                                    <option value="senior">Sênior</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Contrato</label>
                                <select wire:model="slContract" class="input">
                                    <option>CLT</option>
                                    <option>PJ</option>
                                    <option>Estágio</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Região</label>
                                <input type="text" wire:model="slRegion" class="input" placeholder="Ex: São Paulo, remoto">
                            </div>
                        </div>
                    </div>

                    <button wire:click="runSalary" wire:loading.attr="disabled" wire:target="runSalary" class="btn-primary mt-4 w-full">
                        <span wire:loading.remove wire:target="runSalary">✨ Sugerir faixa</span>
                        <span wire:loading wire:target="runSalary">Consultando mercado...</span>
                    </button>

                    @if ($outputSalary !== '')
                        <div class="mt-6 space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">💰 Faixa sugerida</p>
                                <div class="flex gap-2">
                                    <button type="button" @click="copy(@js($outputSalary))" class="btn-ghost text-xs">Copiar</button>
                                    <button type="button" wire:click="clear('salary')" class="btn-ghost text-xs">Limpar</button>
                                </div>
                            </div>
                            <div class="prose prose-sm max-w-none rounded-2xl border border-slate-100 bg-slate-50 p-5 text-slate-800 dark:prose-invert dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-200">
                                {!! \Illuminate\Support\Str::markdown($outputSalary) !!}
                            </div>
                        </div>
                    @endif
                </section>
            @endif

            {{-- ============================================================
                 6. INSIGHTS
                 ============================================================ --}}
            @if ($tab === 'insights')
                <section class="card">
                    <h2 class="mb-1 font-display text-lg font-bold text-slate-900 dark:text-white">Insights automáticos</h2>
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">A IA analisa métricas reais das suas vagas e sugere ações.</p>

                    <button wire:click="runInsights" wire:loading.attr="disabled" wire:target="runInsights" class="btn-primary w-full">
                        <span wire:loading.remove wire:target="runInsights">✨ Gerar insights</span>
                        <span wire:loading wire:target="runInsights">Analisando dados...</span>
                    </button>

                    @if ($outputInsights !== '')
                        <div class="mt-6 space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">📈 Diagnóstico</p>
                                <div class="flex gap-2">
                                    <button type="button" @click="copy(@js($outputInsights))" class="btn-ghost text-xs">Copiar</button>
                                    <button type="button" wire:click="clear('insights')" class="btn-ghost text-xs">Limpar</button>
                                </div>
                            </div>
                            <div class="prose prose-sm max-w-none rounded-2xl border border-slate-100 bg-slate-50 p-5 text-slate-800 dark:prose-invert dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-200">
                                {!! \Illuminate\Support\Str::markdown($outputInsights) !!}
                            </div>
                        </div>
                    @endif
                </section>
            @endif
        </div>
    </div>
</div>
