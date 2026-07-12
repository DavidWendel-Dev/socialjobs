<div class="mx-auto max-w-2xl space-y-4">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <div class="grid h-10 w-10 place-items-center rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-400">
                <x-icon name="bell" class="h-5 w-5"/>
            </div>
            <div>
                <h1 class="font-display text-xl font-bold text-slate-900 dark:text-white">Notificações</h1>
                @if ($unreadCount > 0)
                    <p class="text-xs text-slate-500">
                        {{ $unreadCount }} não {{ $unreadCount === 1 ? 'lida' : 'lidas' }}
                    </p>
                @endif
            </div>
        </div>
        @if ($unreadCount > 0)
            <button wire:click="markAllAsRead" class="btn-secondary text-xs">
                <x-icon name="check" class="mr-1 h-4 w-4"/> Marcar todas
            </button>
        @endif
    </div>

    {{-- Filtros --}}
    <div class="flex items-center gap-2">
        <button wire:click="setFilter('all')"
                class="rounded-full px-3 py-1.5 text-xs font-semibold transition
                       {{ $filter === 'all'
                           ? 'bg-brand-500 text-white'
                           : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
            Todas
        </button>
        <button wire:click="setFilter('unread')"
                class="rounded-full px-3 py-1.5 text-xs font-semibold transition
                       {{ $filter === 'unread'
                           ? 'bg-brand-500 text-white'
                           : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
            Não lidas
            @if ($unreadCount > 0)
                <span class="ml-1 rounded-full bg-white/20 px-1.5 text-[10px]">{{ $unreadCount }}</span>
            @endif
        </button>
    </div>

    {{-- Lista --}}
    <div class="card !p-0 overflow-hidden">
        @forelse ($notifications as $n)
            @php $d = (array) $n->data; @endphp
            <a href="{{ $d['url'] ?? '#' }}"
               wire:click="markAsRead('{{ $n->id }}')"
               wire:navigate
               class="flex items-start gap-3 border-b border-slate-100 px-4 py-4 transition hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/60
                      {{ $n->read_at ? '' : 'bg-brand-50/40 dark:bg-brand-500/10' }}">
                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-500/10 text-brand-600 dark:text-brand-400">
                    <x-icon :name="$d['icon'] ?? 'bell'" class="h-5 w-5"/>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-slate-800 dark:text-slate-100">
                        {{ $d['message'] ?? 'Nova notificação' }}
                    </p>
                    @if (! empty($d['excerpt']))
                        <p class="mt-1 line-clamp-2 text-xs italic text-slate-500">
                            "{{ $d['excerpt'] }}"
                        </p>
                    @endif
                    <p class="mt-1.5 text-[11px] text-slate-400">
                        {{ $n->created_at?->diffForHumans() }}
                    </p>
                </div>
                @if (! $n->read_at)
                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-brand-500" title="Não lida"></span>
                @endif
            </a>
        @empty
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <div class="grid h-16 w-16 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <x-icon name="bell" class="h-8 w-8"/>
                </div>
                <p class="mt-4 font-semibold text-slate-700 dark:text-slate-200">
                    {{ $filter === 'unread' ? 'Sem notificações não lidas' : 'Sem notificações ainda' }}
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Quando alguém interagir com você, aparecerá aqui.
                </p>
            </div>
        @endforelse
    </div>

    @if ($notifications instanceof \Illuminate\Contracts\Pagination\Paginator && $notifications->hasPages())
        <div class="pt-2">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
