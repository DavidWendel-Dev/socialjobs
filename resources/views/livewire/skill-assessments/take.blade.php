<div class="mx-auto max-w-3xl"
     x-data="skillTake({
        state: @js($state),
        limitSeconds: {{ (int) $assessment->duration_minutes * 60 }},
     })"
     x-init="init()"
     x-on:start-generation.window="$wire.generateQuestions()"
     @contextmenu.window="if ($wire.state === 'running') { $event.preventDefault(); registerCopyAttempt() }"
     :class="$wire.state === 'running' ? 'skill-take-locked' : ''">

    {{-- Estilos de proteção — inline para funcionar mesmo se algum CSS falhar --}}
    <style>
        .skill-take-locked, .skill-take-locked * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }
        .skill-take-locked img { -webkit-user-drag: none; -khtml-user-drag: none; -moz-user-drag: none; -o-user-drag: none; user-drag: none; }
        /* Marca d'água nas questões — dificulta screenshot ficar utilizável em imagem */
        .skill-question-body::before {
            content: attr(data-watermark);
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            font-size: 42px;
            color: rgba(148, 163, 184, 0.08);
            font-weight: 900;
            pointer-events: none;
            transform: rotate(-18deg);
            white-space: nowrap;
            z-index: 1;
        }
    </style>

    <script>
        // Componente Alpine com toda a lógica anti-cola.
        // Não usamos apenas eventos DOM — checamos com intervalos regulares.
        function skillTake({ state, limitSeconds }) {
            return {
                elapsed: 0,
                limitSeconds: limitSeconds,
                timer: null,
                integrityTimer: null,
                warningModalOpen: false,
                warningMessage: '',
                devtoolsCheckState: false,

                init() {
                    // Escuta mudanças no state do Livewire
                    this.$watch('$wire.state', v => {
                        if (v === 'running') {
                            this.startProtection();
                        } else {
                            this.stopProtection();
                        }
                    });

                    // Se já entrou em "running" (nunca acontece no mount, mas pra segurança)
                    if (state === 'running') this.startProtection();
                },

                startProtection() {
                    this.elapsed = 0;

                    // Timer regressivo
                    if (this.timer) clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        this.elapsed++;
                        if (this.elapsed >= this.limitSeconds) {
                            this.stopProtection();
                            this.$wire.finish();
                        }
                    }, 1000);

                    // Bind global de eventos anti-cola
                    document.addEventListener('visibilitychange', this._onVisibilityChange = this.handleTabLeave.bind(this));
                    document.addEventListener('copy',       this._onCopy       = this.handleCopy.bind(this));
                    document.addEventListener('cut',        this._onCut        = this.handleCopy.bind(this));
                    document.addEventListener('paste',      this._onPaste      = this.handleCopy.bind(this));
                    document.addEventListener('keydown',    this._onKeyDown    = this.handleKeyDown.bind(this));
                    document.addEventListener('selectstart', this._onSelect    = (e) => e.preventDefault());
                    document.addEventListener('dragstart',  this._onDrag       = (e) => e.preventDefault());
                    window.addEventListener('blur',         this._onWinBlur    = this.handleTabLeave.bind(this));

                    // Detecção contínua de DevTools por diferença de tamanho da janela
                    this.integrityTimer = setInterval(() => this.checkDevtools(), 1500);
                },

                stopProtection() {
                    if (this.timer) { clearInterval(this.timer); this.timer = null; }
                    if (this.integrityTimer) { clearInterval(this.integrityTimer); this.integrityTimer = null; }

                    document.removeEventListener('visibilitychange', this._onVisibilityChange);
                    document.removeEventListener('copy',       this._onCopy);
                    document.removeEventListener('cut',        this._onCut);
                    document.removeEventListener('paste',      this._onPaste);
                    document.removeEventListener('keydown',    this._onKeyDown);
                    document.removeEventListener('selectstart', this._onSelect);
                    document.removeEventListener('dragstart',  this._onDrag);
                    window.removeEventListener('blur',         this._onWinBlur);
                },

                handleTabLeave() {
                    if (this.$wire.state !== 'running') return;
                    // document.hidden só é true no visibilitychange, blur pode ser falso positivo
                    if (document.hidden || document.visibilityState === 'hidden' || document.activeElement === null) {
                        this.$wire.registerTabLeave();
                        this.showWarning('⚠️ Não saia da aba! Após 3 saídas o teste é anulado.');
                    }
                },

                handleCopy(e) {
                    if (this.$wire.state !== 'running') return;
                    e.preventDefault();
                    this.$wire.registerCopyAttempt();
                    this.showWarning('Copiar/colar está bloqueado nesta prova.');
                },

                handleKeyDown(e) {
                    if (this.$wire.state !== 'running') return;
                    const k = e.key.toLowerCase();
                    const combo = (e.ctrlKey || e.metaKey);

                    // Bloqueia atalhos: Ctrl+C, Ctrl+X, Ctrl+V, Ctrl+A, Ctrl+P, Ctrl+S, Ctrl+U
                    if (combo && ['c','x','v','a','p','s','u'].includes(k)) {
                        e.preventDefault();
                        this.$wire.registerCopyAttempt();
                        this.showWarning('Atalhos de cópia/impressão estão bloqueados.');
                        return;
                    }

                    // Bloqueia F12 e Ctrl+Shift+I/J/C (DevTools)
                    if (k === 'f12' || (combo && e.shiftKey && ['i','j','c'].includes(k))) {
                        e.preventDefault();
                        this.$wire.registerDevtoolsOpen();
                        this.showWarning('Ferramentas de desenvolvedor bloqueadas.');
                    }
                },

                checkDevtools() {
                    // Heurística: DevTools aberto muda muito o innerHeight/innerWidth
                    const w = window.outerWidth - window.innerWidth;
                    const h = window.outerHeight - window.innerHeight;
                    const opened = (h > 200 || w > 200);
                    if (opened && !this.devtoolsCheckState) {
                        this.devtoolsCheckState = true;
                        this.$wire.registerDevtoolsOpen();
                        this.showWarning('Feche as ferramentas de desenvolvedor para continuar.');
                    } else if (!opened) {
                        this.devtoolsCheckState = false;
                    }
                },

                showWarning(msg) {
                    this.warningMessage = msg;
                    this.warningModalOpen = true;
                    setTimeout(() => { this.warningModalOpen = false; }, 4000);
                },

                format(s) {
                    if (s < 0) s = 0;
                    const m = Math.floor(s / 60);
                    const r = s % 60;
                    return String(m).padStart(2,'0') + ':' + String(r).padStart(2,'0');
                }
            };
        }
    </script>

    {{-- Modal de warning (aparece quando tenta copiar/sair da aba/abrir devtools) --}}
    <div x-show="warningModalOpen"
         x-cloak
         x-transition
         class="fixed left-1/2 top-6 z-[100] -translate-x-1/2 rounded-full bg-rose-500 px-5 py-2 text-sm font-bold text-white shadow-lg">
        <span x-text="warningMessage"></span>
    </div>

    {{-- ============================================================
         STATE: loading — Groq está gerando as questões
         ============================================================ --}}
    @if ($state === 'loading')
        <div class="overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            <div class="flex flex-col items-center justify-center gap-4 p-10 text-center">
                <div class="relative">
                    <div class="h-16 w-16 animate-spin rounded-full border-4 border-slate-200 border-t-brand-500"></div>
                    <div class="absolute inset-0 grid place-items-center">
                        <svg class="h-6 w-6 text-brand-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2 4 5v7c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V5l-8-3z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="font-display text-lg font-bold">Gerando seu teste com IA...</p>
                    <p class="mt-1 max-w-md text-xs text-slate-500">
                        Nossa IA está criando <strong>20 questões únicas</strong> especialmente para você sobre
                        <strong>{{ $assessment->title }}</strong>. Isso leva de 10 a 30 segundos.
                    </p>
                    <p class="mt-2 text-[10px] text-slate-400">
                        Cada tentativa tem perguntas <strong>diferentes</strong> — impossível colar respostas.
                    </p>
                </div>
            </div>
        </div>

    {{-- ============================================================
         STATE: intro
         ============================================================ --}}
    @elseif ($state === 'intro')
        <div class="overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            @php
                $colorMap = [
                    'brand' => 'from-brand-500 to-brand-600',
                    'blue' => 'from-blue-500 to-blue-600',
                    'amber' => 'from-amber-500 to-amber-600',
                    'accent' => 'from-accent to-orange-500',
                    'rose' => 'from-rose-500 to-rose-600',
                ];
                $gradient = $colorMap[$assessment->color] ?? $colorMap['brand'];
            @endphp
            <div class="relative bg-gradient-to-br {{ $gradient }} p-8 text-white sm:p-10">
                <a href="{{ route('skill-assessments.index') }}"
                   class="absolute right-6 top-6 inline-flex items-center gap-1 rounded-full bg-white/10 px-3 py-1 text-xs font-medium backdrop-blur hover:bg-white/20">
                    ← Voltar
                </a>
                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white/20 backdrop-blur">
                    <x-icon :name="$assessment->icon" class="h-7 w-7"/>
                </div>
                <div class="mt-3 flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider opacity-90">
                    {{ $assessment->category }} · {{ $assessment->difficultyLabel() }}
                </div>
                <h1 class="mt-1 font-display text-2xl font-bold sm:text-3xl">
                    {{ $assessment->title }}
                </h1>
                <p class="mt-2 max-w-xl text-sm opacity-90">
                    {{ $assessment->description ?? $assessment->short_description }}
                </p>
            </div>

            <div class="grid grid-cols-3 gap-3 border-b border-slate-100 p-6 dark:border-slate-800">
                <div class="text-center">
                    <div class="font-display text-xl font-bold">{{ $total > 0 ? $total : $assessment->questions()->count() }}</div>
                    <p class="text-[10px] uppercase tracking-wider text-slate-500">Questões</p>
                </div>
                <div class="text-center">
                    <div class="font-display text-xl font-bold">{{ $assessment->duration_minutes }} min</div>
                    <p class="text-[10px] uppercase tracking-wider text-slate-500">Duração</p>
                </div>
                <div class="text-center">
                    <div class="font-display text-xl font-bold">{{ $assessment->passing_score }}%</div>
                    <p class="text-[10px] uppercase tracking-wider text-slate-500">Aprovação</p>
                </div>
            </div>

            <div class="space-y-4 p-6">
                <div class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    <p class="font-semibold text-slate-900 dark:text-slate-100">Como funciona</p>
                    <ul class="ml-4 list-disc space-y-1 text-xs">
                        <li>🤖 <strong>Cada tentativa tem 20 questões geradas por IA na hora</strong> — únicas pra você, impossível colar de colegas.</li>
                        <li>Você tem <strong>{{ $assessment->duration_minutes }} minutos</strong> para responder.</li>
                        <li>Se atingir <strong>{{ $assessment->passing_score }}%</strong> ou mais, ganha um <strong>badge verificado</strong> no seu perfil e Currículo Digital, além de <strong>+{{ $assessment->xp_reward }} XP</strong>.</li>
                        <li>Você pode <strong>refazer</strong> quantas vezes quiser — o melhor score conta.</li>
                        <li>Ao final, você verá o gabarito com a explicação de cada questão.</li>
                    </ul>
                </div>

                {{-- Aviso de integridade — deixa CLARO que há proteções --}}
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs dark:border-amber-500/30 dark:bg-amber-500/10">
                    <p class="mb-1 flex items-center gap-1 font-bold text-amber-700 dark:text-amber-300">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                        </svg>
                        Regras de integridade
                    </p>
                    <ul class="ml-4 list-disc space-y-0.5 text-slate-700 dark:text-slate-300">
                        <li><strong>Não saia da aba</strong> (Alt+Tab, mudar de janela). 3 saídas anulam o teste.</li>
                        <li>Ordem de questões e alternativas é <strong>aleatória</strong> para cada tentativa.</li>
                        <li>Copiar, imprimir e ferramentas de desenvolvedor estão <strong>bloqueadas</strong>.</li>
                        <li>Comportamento suspeito marca o badge como "não verificado" para recrutadores.</li>
                    </ul>
                </div>

                <button type="button" wire:click="start"
                        class="w-full rounded-xl bg-gradient-to-br {{ $gradient }} px-4 py-3 text-sm font-bold text-white shadow-soft hover:opacity-95">
                    Concordo e começar teste
                </button>
            </div>
        </div>

    {{-- ============================================================
         STATE: running — uma questão por vez
         ============================================================ --}}
    @elseif ($state === 'running' && $current)
        @php
            $qNumber = $currentIndex + 1;
            $progressPct = (int) round(($qNumber / max(1, $total)) * 100);
            $chosen = $currentDisplayed;
        @endphp

        {{-- Barra superior: progresso + timer + contador de violações --}}
        <div class="mb-4 rounded-2xl bg-white p-4 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2 text-xs">
                <span class="font-semibold">{{ $qNumber }} / {{ $total }}</span>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-slate-500">Respondidas: {{ $answered }}</span>
                    @if ($tabLeaves > 0)
                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 font-semibold text-rose-700"
                              title="Saídas da aba detectadas">
                            ⚠️ Saídas: {{ $tabLeaves }}/3
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1 font-mono font-semibold"
                          :class="(limitSeconds - elapsed) < 60 ? 'text-rose-600' : 'text-slate-700 dark:text-slate-200'">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span x-text="format(limitSeconds - elapsed)"></span>
                    </span>
                </div>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div class="h-1.5 rounded-full bg-brand-500 transition-all"
                     style="width: {{ $progressPct }}%"></div>
            </div>
        </div>

        {{-- Card da questão — com marca d'água dificultando screenshot útil --}}
        <div class="relative rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            <div class="skill-question-body relative overflow-hidden"
                 data-watermark="{{ 'ID ' . auth()->id() . ' · SocialJobs' }}">
                <p class="relative z-10 mb-4 text-base font-semibold leading-snug">
                    {{ $current->statement }}
                </p>

                <div class="relative z-10 space-y-2">
                    @foreach ($current->options as $i => $opt)
                        @php $isChosen = ($chosen !== null && (int) $chosen === (int) $i); @endphp
                        <button type="button"
                                wire:click="answerAndNext({{ $current->id }}, {{ $i }})"
                                class="flex w-full items-center gap-3 rounded-xl border p-3 text-left text-sm transition
                                       {{ $isChosen
                                           ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10'
                                           : 'border-slate-200 hover:border-brand-500 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800' }}">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-bold
                                         {{ $isChosen
                                             ? 'bg-brand-500 text-white'
                                             : 'bg-slate-100 text-slate-500 dark:bg-slate-800' }}">
                                {{ chr(65 + $i) }}
                            </span>
                            <span>{{ $opt }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <button type="button" wire:click="previous"
                        @disabled($currentIndex === 0)
                        class="btn-ghost text-xs disabled:opacity-40">
                    ← Anterior
                </button>

                @if ($currentIndex >= $total - 1)
                    <button type="button" wire:click="finish"
                            @disabled($answered < $total)
                            class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-40">
                        Finalizar teste
                    </button>
                @endif
            </div>

            @if ($answered < $total && $currentIndex >= $total - 1)
                <p class="mt-2 text-right text-[10px] text-slate-400">
                    Responda todas as {{ $total }} questões para finalizar ({{ $total - $answered }} faltando).
                </p>
            @endif
        </div>

    {{-- ============================================================
         STATE: result
         ============================================================ --}}
    @elseif ($state === 'result')
        @php
            $isAutoTerm = $lastIntegrity === 'auto_terminated';
            $isSuspicious = $lastIntegrity === 'suspicious';

            $scoreColor = $isAutoTerm
                ? 'from-rose-600 to-slate-700'
                : ($lastPassed ? 'from-brand-500 to-brand-600' : 'from-amber-500 to-rose-500');
        @endphp
        <div class="overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            {{-- Faixa colorida com score --}}
            <div class="relative bg-gradient-to-br {{ $scoreColor }} p-8 text-center text-white sm:p-10">
                @if ($isAutoTerm)
                    <svg class="mx-auto h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <p class="mt-2 text-xs font-bold uppercase tracking-wider opacity-90">Teste anulado</p>
                @elseif ($lastPassed)
                    <svg class="mx-auto h-14 w-14" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2 4 5v7c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V5l-8-3zm-1 15L7 13l1.4-1.4L11 14.2l4.6-4.6L17 11l-6 6z"/>
                    </svg>
                    <p class="mt-2 text-xs font-bold uppercase tracking-wider opacity-90">Aprovado!</p>
                @else
                    <svg class="mx-auto h-14 w-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 9v4"/><path d="M12 17h.01"/>
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    </svg>
                    <p class="mt-2 text-xs font-bold uppercase tracking-wider opacity-90">Continue tentando</p>
                @endif

                <div class="mt-4 font-display text-6xl font-black">{{ $lastScore }}<span class="text-3xl opacity-70">/100</span></div>
                <p class="mt-1 text-sm opacity-90">
                    Nota mínima: {{ $assessment->passing_score }}%
                </p>
            </div>

            <div class="space-y-4 p-6">
                @if ($isAutoTerm)
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm dark:border-rose-500/30 dark:bg-rose-500/10">
                        <p class="font-semibold text-rose-800 dark:text-rose-300">Teste anulado por violação de integridade.</p>
                        <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                            Detectamos várias saídas da aba, tentativas de copiar ou abertura de DevTools. Nenhum badge foi concedido. Você pode refazer.
                        </p>
                    </div>
                @elseif ($isSuspicious && $lastPassed)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm dark:border-amber-500/30 dark:bg-amber-500/10">
                        <p class="font-semibold text-amber-800 dark:text-amber-300">Aprovado com asterisco.</p>
                        <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                            Detectamos comportamento suspeito ({{ $tabLeaves }} saídas, {{ $copyAttempts }} tentativas de copiar).
                            O badge fica com uma flag de "revisão" para recrutadores.
                        </p>
                    </div>
                @elseif ($lastPassed)
                    <div class="rounded-xl border border-brand-200 bg-brand-50/50 p-4 text-sm dark:border-brand-500/30 dark:bg-brand-500/10">
                        <div class="flex items-center gap-2 font-semibold text-brand-700 dark:text-brand-300">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2 4 5v7c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V5l-8-3z"/>
                            </svg>
                            Badge desbloqueado
                        </div>
                        <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                            Seu badge <strong>{{ $assessment->title }} · {{ $lastScore }}/100</strong> agora aparece no seu perfil e Currículo Digital.
                            Você ganhou <strong>+{{ $assessment->xp_reward }} XP</strong>.
                        </p>
                    </div>
                @else
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm dark:border-amber-500/30 dark:bg-amber-500/10">
                        <p class="font-semibold text-amber-800 dark:text-amber-300">Você não atingiu a nota mínima ({{ $assessment->passing_score }}%).</p>
                        <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                            Revise as questões abaixo e tente novamente — seu melhor score é o que conta.
                        </p>
                    </div>
                @endif

                {{-- Ações --}}
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <button type="button" wire:click="restart"
                            class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">
                        Refazer teste
                    </button>
                    <a href="{{ route('skill-assessments.index') }}"
                       class="btn-ghost text-sm">
                        Ver outros testes
                    </a>
                    <a href="{{ url('/u/' . (auth()->user()->username ?? auth()->user()->id) . '?tab=curriculum') }}"
                       class="btn-ghost text-sm">
                        Ver meu Currículo →
                    </a>
                </div>

                {{-- Gabarito com explicações --}}
                <div class="border-t border-slate-100 pt-4 dark:border-slate-800">
                    <p class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500">
                        Revisão das questões
                    </p>
                    <div class="space-y-3">
                        @foreach ($questions as $i => $q)
                            @php
                                // Aceita tanto stdClass (via questionsCollection) quanto array puro
                                // (caso Livewire re-hidrate a coleção como array associativo).
                                $qArr           = is_array($q) ? $q : (array) $q;
                                $qId            = $qArr['id'] ?? null;
                                $qStatement     = (string) ($qArr['statement'] ?? '');
                                $qOptions       = (array) ($qArr['options'] ?? []);
                                $qCorrectIndex  = (int) ($qArr['correct_index'] ?? -1);
                                $qExplanation   = (string) ($qArr['explanation'] ?? '');

                                $userDisplayedAns = $qId !== null ? ($displayedAnswers[$qId] ?? null) : null;
                                $isRight = $userDisplayedAns !== null && (int) $userDisplayedAns === $qCorrectIndex;
                            @endphp
                            <div class="rounded-xl border p-3 text-sm
                                        {{ $isRight
                                            ? 'border-brand-200 bg-brand-50/30 dark:border-brand-500/20 dark:bg-brand-500/5'
                                            : 'border-rose-200 bg-rose-50/30 dark:border-rose-500/20 dark:bg-rose-500/5' }}">
                                <p class="flex items-start gap-2 font-semibold">
                                    <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full text-[10px] font-bold text-white
                                                 {{ $isRight ? 'bg-brand-500' : 'bg-rose-500' }}">
                                        {{ $i + 1 }}
                                    </span>
                                    {{ $qStatement }}
                                </p>

                                <ul class="mt-2 space-y-1 pl-7 text-xs">
                                    @foreach ($qOptions as $oi => $opt)
                                        @php
                                            $isCorrect = (int) $oi === $qCorrectIndex;
                                            $isUserChoice = $userDisplayedAns !== null && (int) $userDisplayedAns === (int) $oi;
                                        @endphp
                                        <li class="flex items-start gap-2
                                                   {{ $isCorrect
                                                       ? 'font-semibold text-brand-700 dark:text-brand-300'
                                                       : ($isUserChoice ? 'text-rose-600 line-through' : 'text-slate-500') }}">
                                            <span>{{ chr(65 + $oi) }}.</span>
                                            <span>{{ $opt }}</span>
                                            @if ($isCorrect)
                                                <span class="text-brand-500">✓</span>
                                            @elseif ($isUserChoice)
                                                <span class="text-rose-500">✗ sua resposta</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>

                                @if ($qExplanation)
                                    <p class="mt-2 rounded-lg bg-white p-2 pl-7 text-xs text-slate-600 dark:bg-slate-800/50 dark:text-slate-300">
                                        <strong class="text-brand-600">Explicação:</strong> {{ $qExplanation }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
