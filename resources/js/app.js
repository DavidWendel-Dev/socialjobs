import './bootstrap';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Só inicializa o Echo se VITE_REVERB_ENABLED estiver marcado como "true"
// e a chave/host estiverem configurados. Assim evitamos tentativas
// infinitas de conexão WebSocket em dev quando o Reverb não está rodando.
const reverbEnabled = String(import.meta.env.VITE_REVERB_ENABLED ?? 'false') === 'true';
const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST;

if (reverbEnabled && reverbKey && reverbHost) {
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost,
            wsPort: import.meta.env.VITE_REVERB_PORT,
            wssPort: import.meta.env.VITE_REVERB_PORT,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    } catch (e) {
        // eslint-disable-next-line no-console
        console.warn('Echo/Reverb não pôde inicializar:', e?.message ?? e);
    }
}

// Theme toggle helper (Alpine also handles it, but we expose global to persist between visits)
window.applyStoredTheme = () => {
    const stored = localStorage.getItem('SocialJobs-theme');
    if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

window.applyStoredTheme();

/**
 * Registra componentes Alpine reutilizáveis.
 *
 * Motivo: `x-data` inline no HTML precisa lidar com aspas duplas e caracteres
 * como '<', '>', '&' — que o parser HTML confunde com tags. Ao registrar aqui,
 * mantemos o HTML limpo (`x-data="mentionComposer"`) e escrevemos JS "de verdade".
 */
document.addEventListener('alpine:init', () => {
    // Fábrica compartilhada entre Composer e Comments (mesma lógica de menção @).
    const buildMentionable = (refName) => ({
        localBody: '',
        showMentions: false,
        currentMentionStart: -1,
        showMusicPanel: false,

        /**
         * Chamado a cada input. Se o cursor está logo depois de "@algo",
         * abre o dropdown e envia o termo pro backend Livewire.
         */
        onInput(e) {
            const input = e.target;
            const cursor = input.selectionStart;
            const before = this.localBody.substring(0, cursor);
            const match = before.match(/(^|\s)@([a-z0-9._-]{0,39})$/i);

            if (match) {
                this.currentMentionStart = before.length - match[2].length - 1;
                this.$wire.set('mentionQuery', match[2]);
                this.showMentions = true;
            } else {
                this.showMentions = false;
                this.currentMentionStart = -1;
                if (this.$wire.mentionQuery !== '') {
                    this.$wire.closeMentions();
                }
            }

            // Auto-grow para textarea
            if (input.tagName === 'TEXTAREA') {
                input.style.height = 'auto';
                input.style.height = input.scrollHeight + 'px';
            }
        },

        /**
         * Substitui `@parcial` pelo `@username ` completo.
         */
        pickMention(username) {
            if (this.currentMentionStart === -1) return;
            const before = this.localBody.substring(0, this.currentMentionStart);
            const after = this.localBody
                .substring(this.currentMentionStart)
                .replace(/@[a-z0-9._-]*/i, '');
            this.localBody = before + '@' + username + ' ' + after.replace(/^\s+/, '');
            this.showMentions = false;
            this.currentMentionStart = -1;
            this.$wire.closeMentions();
            this.$nextTick(() => this.$refs[refName]?.focus());
        },
    });

    // Componente Alpine para o compositor de posts
    window.Alpine.data('postComposer', () => buildMentionable('postBody'));

    // Componente Alpine para o compositor de comentários
    window.Alpine.data('commentComposer', () => buildMentionable('commentInput'));
});
