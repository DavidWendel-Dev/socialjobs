<div class="grid grid-cols-12 gap-4">
    {{-- Sidebar de conversas --}}
    <aside class="col-span-12 md:col-span-5 lg:col-span-4">
        <div class="card sticky top-24 !p-3">
            <div class="flex items-center justify-between px-1">
                <h2 class="font-display text-lg font-bold">Mensagens</h2>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500 dark:bg-slate-800">
                    {{ $conversations->count() }}
                </span>
            </div>

            <div class="mt-3">
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar conversa..."
                       class="input">
            </div>

            <ul class="mt-3 space-y-1">
                @forelse ($conversations as $c)
                    <li>
                        <button wire:click="open({{ $c->id }})"
                                class="flex w-full items-start gap-3 rounded-xl p-2 text-left transition
                                       {{ $active?->id === $c->id
                                           ? 'bg-brand-50 dark:bg-brand-500/10'
                                           : 'hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                            <x-avatar :user="$c->other_user" size="md"/>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">
                                    {{ $c->other_user?->name ?? 'Conversa' }}
                                </p>
                                <p class="truncate text-xs text-slate-500">
                                    {{ \Illuminate\Support\Str::limit($c->last_message?->body ?? 'Sem mensagens ainda', 40) }}
                                </p>
                            </div>
                            @if ($c->last_message)
                                <span class="shrink-0 text-[10px] text-slate-400">
                                    {{ $c->last_message->created_at->diffForHumans(null, true) }}
                                </span>
                            @endif
                        </button>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-xs text-slate-500 dark:border-slate-700">
                        Você ainda não tem conversas.<br>
                        Abra o perfil de alguém e clique em "Mensagem".
                    </li>
                @endforelse
            </ul>
        </div>
    </aside>

    {{-- Painel de chat --}}
    <section class="col-span-12 md:col-span-7 lg:col-span-8">
        @if ($active)
            <livewire:messaging.thread :conversation-id="$active->id" :key="'thread-' . $active->id"/>
        @else
            <div class="card grid min-h-[420px] place-items-center text-center">
                <div>
                    <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-brand-500/10 text-brand-600">
                        <x-icon name="message" class="h-6 w-6"/>
                    </div>
                    <h3 class="font-display text-lg font-bold">Selecione uma conversa</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Clique em uma conversa à esquerda para começar.
                    </p>
                </div>
            </div>
        @endif
    </section>
</div>
