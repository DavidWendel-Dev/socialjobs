{{--
    Sino de notificações — dropdown do header.
    - Badge vermelha só aparece se houver não lidas
    - Lista as últimas 10, com destaque nas não lidas
    - Botão "marcar todas" + link "ver todas"
--}}
<div x-data="{ open: @entangle('open') }" @keydown.escape.window="open = false" class="relative flex-shrink-0">
    {{-- BOTÃO DO SINO --}}
    <button @click="open = !open"
            class="relative grid h-9 w-9 place-items-center rounded-xl text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
            aria-label="Notificações">
        <x-icon name="bell" class="h-5 w-5"/>
        @if ($this->unreadCount > 0)
            <span class="absolute -right-0.5 -top-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900">
                {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    {{-- DROPDOWN --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         @click.outside="open = false"
         x-cloak
         class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800">
            <div>
                <p class="font-display text-sm font-bold text-slate-900 dark:text-white">Notificações</p>
                @if ($this->unreadCount > 0)
                    <p class="text-[11px] text-slate-500">{{ $this->unreadCount }} {{ $this->unreadCount === 1 ? 'nova' : 'novas' }}</p>
                @endif
            </div>
            @if ($this->unreadCount > 0)
                <button wire:click="markAllAsRead"
                        class="text-[11px] font-semibold text-brand-600 hover:underline dark:text-brand-400">
                    Marcar todas
                </button>
            @endif
        </div>

        {{-- Lista --}}
        <div class="max-h-96 overflow-y-auto">
            @forelse ($notifications as $n)
                @php $d = (array) $n->data; @endphp
                <a href="{{ $d['url'] ?? '#' }}"
                   wire:click="markAsRead('{{ $n->id }}')"
                   class="flex items-start gap-3 border-b border-slate-100 px-4 py-3 transition hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/60
                          {{ $n->read_at ? '' : 'bg-brand-50/50 dark:bg-brand-500/10' }}">
                    <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand-500/10 text-brand-600 dark:text-brand-400">
                        <x-icon :name="$d['icon'] ?? 'bell'" class="h-4 w-4"/>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="line-clamp-2 text-xs text-slate-700 dark:text-slate-200">
                            <span class="font-semibold">{{ $d['actor_name'] ?? '' }}</span>
                            {{ str_replace($d['actor_name'] ?? '', '', $d['message'] ?? '') }}
                        </p>
                        @if (! empty($d['excerpt']))
                            <p class="mt-0.5 line-clamp-1 text-[11px] italic text-slate-500">
                                "{{ $d['excerpt'] }}"
                            </p>
                        @endif
                        <p class="mt-1 text-[10px] text-slate-400">
                            {{ $n->created_at?->diffForHumans() }}
                        </p>
                    </div>
                    @if (! $n->read_at)
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-brand-500"></span>
                    @endif
                </a>
            @empty
                <div class="flex flex-col items-center justify-center px-6 py-10 text-center">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                        <x-icon name="bell" class="h-6 w-6"/>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Sem notificações ainda
                    </p>
                    <p class="mt-1 text-[11px] text-slate-500">
                        Quando alguém interagir com você, aparecerá aqui.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if ($notifications->isNotEmpty())
            <a href="{{ route('notifications.index') }}"
               class="block border-t border-slate-100 px-4 py-2.5 text-center text-xs font-semibold text-brand-600 hover:bg-slate-50 dark:border-slate-800 dark:text-brand-400 dark:hover:bg-slate-800">
                Ver todas as notificações
            </a>
        @endif
    </div>
</div>
