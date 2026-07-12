<div class="card flex h-[70vh] min-h-[500px] flex-col overflow-hidden !p-0"
     x-data="{ body: @entangle('body'), menuOpen: false }">
    @if ($conversation && $other)
        {{-- Cabeçalho da conversa --}}
        <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3 dark:border-slate-800">
            <x-avatar :user="$other" size="md"/>
            <div class="min-w-0 flex-1">
                <p class="flex items-center gap-1.5 truncate font-semibold">
                    <span class="truncate">{{ $other->display_name ?? $other->name }}</span>
                    @if ($isMuted)
                        {{-- Sino cortado (silenciado) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" title="Silenciado">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17H5a1 1 0 0 1-.8-1.6l1.3-1.7A6 6 0 0 0 6.5 10V9a5.5 5.5 0 0 1 3.5-5.1"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 3.5A5.5 5.5 0 0 1 17.5 9v1a6 6 0 0 0 1.2 3.6"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20a2 2 0 0 0 4 0"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/>
                        </svg>
                    @endif
                    @if ($isBlocked)
                        {{-- Círculo com barra (bloqueado) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" title="Bloqueado">
                            <circle cx="12" cy="12" r="9"/>
                            <path stroke-linecap="round" d="M5.6 5.6l12.8 12.8"/>
                        </svg>
                    @endif
                </p>
                @if ($other->headline)
                    <p class="truncate text-xs text-slate-500">{{ $other->headline }}</p>
                @endif
            </div>

            @php
                $isCompanyOther = ($other->type ?? 'candidate') === 'company';
                $companySlug    = $isCompanyOther ? optional($other->companyProfile)->slug : null;
                $profileUrl     = $isCompanyOther && $companySlug
                    ? url('/c/' . $companySlug)
                    : url('/u/' . ($other->username ?? $other->id));
            @endphp

            <a href="{{ $profileUrl }}"
               class="hidden text-xs font-medium text-brand-600 hover:underline sm:inline">
                Ver perfil
            </a>

            {{-- Menu de 3 pontinhos --}}
            <div class="relative" @click.outside="menuOpen = false">
                <button type="button" @click="menuOpen = !menuOpen"
                        class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                        title="Mais opções">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="5" r="1.75"/><circle cx="12" cy="12" r="1.75"/><circle cx="12" cy="19" r="1.75"/>
                    </svg>
                </button>
                <div x-show="menuOpen" x-transition x-cloak
                     class="absolute right-0 top-full z-20 mt-1 w-56 overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                    <a href="{{ $profileUrl }}"
                       class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
                        <x-icon name="user" class="h-4 w-4"/> Ver perfil
                    </a>

                    @if ($isMuted)
                        <button type="button" wire:click="unmute" @click="menuOpen = false"
                                class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
                            <x-icon name="bell" class="h-4 w-4"/> Ativar notificações
                        </button>
                    @else
                        <button type="button" wire:click="mute" @click="menuOpen = false"
                                class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">
                            <x-icon name="bell" class="h-4 w-4"/> Silenciar notificações
                        </button>
                    @endif

                    <div class="border-t border-slate-100 dark:border-slate-700"></div>

                    @if ($isBlocked)
                        <button type="button" wire:click="unblock" @click="menuOpen = false"
                                class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10">
                            <x-icon name="check" class="h-4 w-4"/> Desbloquear
                        </button>
                    @else
                        <button type="button"
                                wire:click="block"
                                wire:confirm="Tem certeza que deseja bloquear {{ $other->display_name }}? Vocês não poderão mais trocar mensagens nem ver posts um do outro."
                                @click="menuOpen = false"
                                class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">
                            <x-icon name="x" class="h-4 w-4"/> Bloquear usuário
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Flash --}}
        @if ($flash)
            <div class="border-b border-amber-200 bg-amber-50 px-4 py-2 text-xs text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                {{ $flash }}
            </div>
        @endif

        {{-- Lista de mensagens --}}
        <div class="flex-1 overflow-y-auto space-y-2 bg-slate-50 p-4 dark:bg-slate-950/40"
             x-data
             x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
             wire:key="messages-{{ $conversation->id }}-{{ $messages->count() }}">
            @forelse ($messages as $m)
                @php $mine = $m->user_id === auth()->id(); @endphp
                <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%]">
                        <div class="rounded-2xl px-4 py-2 text-sm
                                    {{ $mine
                                        ? 'bg-brand-500 text-white rounded-br-sm'
                                        : 'bg-white text-slate-800 shadow-sm rounded-bl-sm dark:bg-slate-800 dark:text-slate-100' }}">
                            {{ $m->body }}
                        </div>
                        <p class="mt-1 flex items-center gap-1 text-[10px] text-slate-400 {{ $mine ? 'justify-end' : 'justify-start' }}">
                            <span>{{ $m->created_at->format('H:i') }}</span>
                            @if ($mine)
                                {{-- Tracinhos estilo WhatsApp:
                                     • 1 tick cinza  → enviado
                                     • 2 ticks azuis → lido pelo destinatário --}}
                                @if ($m->read_at)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 12l4 4 6-8"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 16l4 4 9-13"/>
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l5 5L20 6"/>
                                    </svg>
                                @endif
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <p class="mt-8 text-center text-sm text-slate-500">
                    Sem mensagens ainda. Diga oi!
                </p>
            @endforelse
        </div>

        {{-- Compositor de mensagem --}}
        @if ($isBlocked)
            <div class="border-t border-slate-100 bg-rose-50 p-3 text-center text-xs text-rose-700 dark:border-slate-800 dark:bg-rose-500/10 dark:text-rose-300">
                Você bloqueou este usuário. Desbloqueie no menu para trocar mensagens.
            </div>
        @elseif ($blockedBy)
            <div class="border-t border-slate-100 bg-slate-50 p-3 text-center text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-800">
                Não é possível enviar mensagens para este usuário.
            </div>
        @else
            <form wire:submit.prevent="send"
                  class="flex items-center gap-2 border-t border-slate-100 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                <input type="text" x-model="body"
                       placeholder="Escreva uma mensagem..."
                       class="input flex-1"
                       autocomplete="off">
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="btn-primary shrink-0 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!body || !body.trim()">
                    Enviar
                </button>
            </form>
        @endif
    @else
        <div class="grid flex-1 place-items-center text-sm text-slate-500">
            Conversa indisponível ou você não é participante.
        </div>
    @endif
</div>
