{{--
    Widget de chat flutuante — estilo Facebook Messenger clássico.
    - Fechado: aba retangular colada no canto inferior direito, cantos superiores arredondados.
    - Aberto: mesma aba vira o header do painel de conversas, que sobe pra cima.
    - Puro HTML + Alpine + fetch API (sem Livewire).
--}}
<script data-cfasync="false">
    window.chatDock = function () {
        return {
            open: false,
            loading: false,
            sending: false,
            conversations: [],
            activeId: null,
            other: {},
            messages: [],
            body: '',
            isBlocked: false,
            blockedBy: false,
            blockedFlash: null,

            init() {
                window.addEventListener('open-chat-with', (e) => {
                    const uid = e.detail?.userId;
                    if (uid) this.startDm(uid);
                });
            },

            initials(name) {
                if (!name) return 'U';
                return name.split(' ').filter(Boolean).slice(0, 2).map(p => p[0].toUpperCase()).join('');
            },

            async togglePanel() {
                this.open = !this.open;
                if (this.open) {
                    this.activeId = null;
                    await this.loadConversations();
                }
            },

            async loadConversations() {
                this.loading = true;
                try {
                    const r = await fetch('/chat-dock/conversations', { headers: { 'Accept': 'application/json' } });
                    if (r.ok) {
                        const j = await r.json();
                        this.conversations = j.conversations || [];
                    }
                } catch (err) { console.error('[chatDock] loadConversations', err); }
                this.loading = false;
            },

            async openThread(id) {
                this.activeId = id;
                this.messages = [];
                this.isBlocked = false;
                this.blockedBy = false;
                this.blockedFlash = null;
                try {
                    const r = await fetch('/chat-dock/conversations/' + id, { headers: { 'Accept': 'application/json' } });
                    if (r.ok) {
                        const j = await r.json();
                        this.messages  = j.messages || [];
                        this.other     = j.other || {};
                        this.isBlocked = !!j.is_blocked;
                        this.blockedBy = !!j.blocked_by;
                        this.$nextTick(() => {
                            const c = this.$refs.msgsContainer;
                            if (c) c.scrollTop = c.scrollHeight;
                        });
                    }
                } catch (err) { console.error('[chatDock] openThread', err); }
            },

            closeThread() {
                this.activeId = null;
                this.other = {};
                this.messages = [];
                this.loadConversations();
            },

            async sendMessage() {
                const text = this.body.trim();
                if (!text || this.sending) return;
                this.sending = true;
                this.blockedFlash = null;
                try {
                    const r = await fetch('/chat-dock/conversations/' + this.activeId + '/send', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({ body: text }),
                    });
                    if (r.status === 403) {
                        // bloqueio ativo — atualiza flags
                        this.blockedFlash = 'Vocês não podem trocar mensagens (bloqueio ativo).';
                        this.openThread(this.activeId);
                        return;
                    }
                    if (r.ok) {
                        const j = await r.json();
                        if (j.message) this.messages.push(j.message);
                        this.body = '';
                        this.$nextTick(() => {
                            const c = this.$refs.msgsContainer;
                            if (c) c.scrollTop = c.scrollHeight;
                        });
                    }
                } catch (err) { console.error('[chatDock] sendMessage', err); }
                this.sending = false;
            },

            async startDm(userId) {
                try {
                    const r = await fetch('/chat-dock/start-dm', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({ user_id: userId }),
                    });
                    if (r.ok) {
                        const j = await r.json();
                        this.open = true;
                        this.openThread(j.conversation_id);
                    }
                } catch (err) { console.error('[chatDock] startDm', err); }
            },
        };
    };
</script>

<div
    id="chat-dock-root"
    class="hidden sm:block"
    style="position: fixed; bottom: 0; right: 28px; z-index: 2147483647; width: 320px;"
    x-data="chatDock()"
    x-init="init(); document.body.appendChild($el);"
>
    {{-- ==================== PAINEL (só existe quando aberto) ==================== --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="mb-2 rounded-2xl overflow-hidden bg-white shadow-2xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700"
        style="display: none"
    >
        {{-- ============= HEADER (a "aba") ============= --}}
        <div
            @click="!activeId && togglePanel()"
            class="flex items-center justify-between gap-2 bg-gradient-to-r from-brand-500 to-emerald-600 px-4 py-3 text-white cursor-pointer select-none"
        >
            {{-- MODO THREAD --}}
            <template x-if="activeId">
                <div class="flex min-w-0 flex-1 items-center gap-2">
                    <button
                        @click.stop="closeThread()"
                        class="rounded-lg p-1 transition hover:bg-white/20"
                        title="Voltar"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                    <a :href="other.profile_url || '#'" @click.stop class="flex min-w-0 flex-1 items-center gap-2 hover:opacity-90">
                        <template x-if="other.avatar">
                            <img :src="other.avatar" class="h-8 w-8 rounded-full object-cover" :alt="other.name">
                        </template>
                        <template x-if="!other.avatar">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-white/20 text-xs font-bold" x-text="initials(other.name)"></span>
                        </template>
                        <p class="truncate text-sm font-semibold" x-text="other.name"></p>
                    </a>
                </div>
            </template>

            {{-- MODO LISTA --}}
            <template x-if="!activeId">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.068.157 2.148.279 3.238.364.466.037.893.281 1.153.671L12 21l2.652-3.978c.26-.39.687-.634 1.153-.67 1.09-.086 2.17-.208 3.238-.365 1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
                    </svg>
                    <p class="font-display text-sm font-bold">Mensagens</p>
                </div>
            </template>

            <div class="flex items-center gap-1">
                <template x-if="!activeId">
                    <a href="{{ route('messages.index') }}" @click.stop class="text-[11px] font-semibold underline opacity-90 hover:opacity-100">
                        Abrir tudo
                    </a>
                </template>
                <button @click.stop="togglePanel()" class="rounded-lg p-1 transition hover:bg-white/20" title="Fechar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ==================== CONTEÚDO — THREAD ==================== --}}
        <div x-show="activeId" class="flex h-[440px] flex-col">
            <div
                x-ref="msgsContainer"
                class="flex-1 space-y-2 overflow-y-auto p-3 bg-slate-50 dark:bg-slate-900/50"
            >
                <template x-if="messages.length === 0">
                    <div class="mt-8 text-center text-xs text-slate-400">
                        <p>Sem mensagens ainda.</p>
                        <p>Diga oi 👋</p>
                    </div>
                </template>
                <template x-for="msg in messages" :key="msg.id">
                    <div class="flex" :class="msg.mine ? 'justify-end' : 'justify-start'">
                        <div
                            class="max-w-[80%] rounded-2xl px-3 py-2 text-xs leading-relaxed"
                            :class="msg.mine
                                ? 'bg-brand-500 text-white rounded-br-sm'
                                : 'bg-white text-slate-800 rounded-bl-sm shadow-sm dark:bg-slate-800 dark:text-slate-100'"
                        >
                            <p x-text="msg.body" class="whitespace-pre-wrap break-words"></p>
                            <p class="mt-0.5 flex items-center gap-1 text-[9px]"
                               :class="msg.mine ? 'text-white/70 justify-end' : 'text-slate-500 justify-start'">
                                <span x-text="msg.created_at"></span>
                                <template x-if="msg.mine && msg.read">
                                    {{-- 2 ticks (lido) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-sky-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 12l4 4 6-8"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 16l4 4 9-13"/>
                                    </svg>
                                </template>
                                <template x-if="msg.mine && !msg.read">
                                    {{-- 1 tick (enviado) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l5 5L20 6"/>
                                    </svg>
                                </template>
                            </p>
                        </div>
                    </div>
                </template>
            </div>

            <form x-show="!isBlocked && !blockedBy" @submit.prevent="sendMessage()"
                  class="border-t border-slate-100 bg-white p-2 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-2">
                    <input type="text"
                           x-model="body"
                           placeholder="Escreva uma mensagem…"
                           autocomplete="off"
                           :disabled="sending"
                           class="flex-1 rounded-full border-slate-200 bg-slate-50 px-3 py-1.5 text-xs focus:border-brand-500 focus:ring-brand-500 disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-white"/>
                    <button type="submit"
                            :disabled="!body.trim() || sending"
                            class="inline-grid h-8 w-8 place-items-center rounded-full bg-brand-500 text-white transition hover:bg-brand-600 disabled:opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                </div>
            </form>

            {{-- Aviso: eu bloqueei --}}
            <div x-show="isBlocked" class="border-t border-slate-100 bg-rose-50 p-3 text-center text-[11px] text-rose-700 dark:border-slate-800 dark:bg-rose-500/10 dark:text-rose-300">
                Você bloqueou este usuário. Desbloqueie no perfil ou na página de mensagens para conversar.
            </div>

            {{-- Aviso: fui bloqueado --}}
            <div x-show="!isBlocked && blockedBy" class="border-t border-slate-100 bg-slate-50 p-3 text-center text-[11px] text-slate-500 dark:border-slate-800 dark:bg-slate-800">
                Não é possível enviar mensagens para este usuário.
            </div>

            {{-- Flash de bloqueio (ex: bloqueado após envio) --}}
            <div x-show="blockedFlash" class="border-t border-slate-100 bg-amber-50 p-2 text-center text-[10px] text-amber-800 dark:border-slate-800 dark:bg-amber-500/10 dark:text-amber-300"
                 x-text="blockedFlash"></div>
        </div>

        {{-- ==================== CONTEÚDO — LISTA ==================== --}}
        <div x-show="!activeId" class="h-[440px] overflow-y-auto bg-white dark:bg-slate-900">
            <template x-if="loading">
                <div class="flex h-full items-center justify-center">
                    <p class="text-xs text-slate-400">Carregando…</p>
                </div>
            </template>

            <template x-if="!loading && conversations.length === 0">
                <div class="flex h-full flex-col items-center justify-center gap-3 px-6 text-center">
                    <div class="grid h-14 w-14 place-items-center rounded-2xl bg-brand-500/10 text-brand-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.068.157 2.148.279 3.238.364.466.037.893.281 1.153.671L12 21l2.652-3.978c.26-.39.687-.634 1.153-.67 1.09-.086 2.17-.208 3.238-.365 1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Nenhuma conversa</p>
                    <p class="text-xs text-slate-500">Vá em qualquer perfil e clique em <b>Mensagem</b>.</p>
                    <a href="{{ route('feed') }}" class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-brand-500 px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-600">
                        Ir para o feed
                    </a>
                </div>
            </template>

            <template x-for="c in conversations" :key="c.id">
                <button
                    @click="openThread(c.id)"
                    class="flex w-full items-center gap-3 border-b border-slate-100 px-3 py-2.5 text-left transition hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
                >
                    <template x-if="c.other_avatar">
                        <img :src="c.other_avatar" class="h-10 w-10 rounded-full object-cover" :alt="c.other_name">
                    </template>
                    <template x-if="!c.other_avatar">
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-brand-500 text-xs font-bold text-white" x-text="initials(c.other_name)"></span>
                    </template>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white" x-text="c.other_name"></p>
                        <p class="truncate text-xs text-slate-500" x-text="c.last_message"></p>
                    </div>
                    <span class="shrink-0 text-[10px] text-slate-400" x-text="c.updated_at"></span>
                </button>
            </template>
        </div>
    </div>

    {{-- ==================== ABA COLADA NO FUNDO (só quando fechado) ==================== --}}
    <button
        x-show="!open"
        @click="togglePanel()"
        type="button"
        class="mb-2 flex w-full items-center justify-between gap-2 rounded-2xl bg-gradient-to-r from-brand-500 to-emerald-600 px-4 py-3 text-left text-white shadow-2xl transition hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-white/40"
        title="Abrir mensagens"
    >
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.068.157 2.148.279 3.238.364.466.037.893.281 1.153.671L12 21l2.652-3.978c.26-.39.687-.634 1.153-.67 1.09-.086 2.17-.208 3.238-.365 1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
            </svg>
            <span class="font-display text-sm font-bold">Mensagens</span>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/>
        </svg>
    </button>
</div>
