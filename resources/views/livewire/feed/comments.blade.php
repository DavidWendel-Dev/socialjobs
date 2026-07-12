<div class="space-y-4">
    {{-- Cabeçalho: contador --}}
    @if ($total > 0)
        <p class="text-xs font-medium text-slate-500">
            {{ $total }} {{ $total === 1 ? 'comentário' : 'comentários' }}
        </p>
    @endif

    {{-- Lista aninhada de comentários --}}
    @if ($roots->count())
        <ul class="space-y-3">
            @foreach ($roots as $comment)
                @include('livewire.feed.partials.comment', ['comment' => $comment, 'depth' => 0])
            @endforeach
        </ul>
    @else
        <p class="text-sm text-slate-500">Seja o primeiro a comentar 👋</p>
    @endif

    {{-- ============================================================
         Compositor com menções (@) e resposta
         ============================================================ --}}
    @auth
        {{-- Aviso quando estou respondendo --}}
        @if ($replyingTo)
            <div class="flex items-center justify-between rounded-xl bg-brand-50 px-3 py-1.5 text-xs dark:bg-brand-500/10">
                <span class="text-brand-700 dark:text-brand-300">
                    Respondendo a <strong>{{ $replyingToName }}</strong>
                </span>
                <button type="button" wire:click="cancelReply"
                        class="text-brand-600 hover:underline">Cancelar</button>
            </div>
        @endif

        <form wire:submit.prevent="submit"
              x-data="commentComposer"
              x-init="localBody = $wire.body || ''"
              class="relative flex items-start gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
            <x-avatar :user="auth()->user()" size="sm"/>

            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <input type="text"
                           x-ref="commentInput"
                           x-model="localBody"
                           @input="onInput($event); $wire.set('body', localBody)"
                           @keydown.enter.prevent="if (localBody.trim() !== '' && !showMentions) $wire.submit()"
                           @keydown.escape="showMentions = false; $wire.closeMentions()"
                           placeholder="Escreva um comentário... use @ para mencionar"
                           class="input"
                           maxlength="2000"
                           autocomplete="off">
                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                            :disabled="localBody.trim() === ''"
                            :class="localBody.trim() === '' ? 'opacity-50 cursor-not-allowed' : ''"
                            class="btn-primary shrink-0">
                        <span wire:loading.remove wire:target="submit">Enviar</span>
                        <span wire:loading wire:target="submit">...</span>
                    </button>
                </div>

                {{-- Dropdown de menções --}}
                <div x-show="showMentions && $wire.mentionResults && $wire.mentionResults.length > 0"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-cloak
                     class="absolute left-10 right-0 top-full z-20 mt-1 max-h-64 overflow-y-auto rounded-2xl bg-white p-1 shadow-soft-lg ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700">
                    @foreach ($mentionResults as $user)
                        <button type="button"
                                @click.stop="pickMention(@js($user['username']))"
                                class="flex w-full items-center gap-2.5 rounded-xl p-2 text-left hover:bg-brand-50 dark:hover:bg-brand-500/10">
                            @if (! empty($user['avatar_url']))
                                <img src="{{ $user['avatar_url'] }}" alt=""
                                     class="h-8 w-8 rounded-full object-cover">
                            @else
                                <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-100 text-xs font-bold text-brand-700 dark:bg-brand-500/20">
                                    {{ mb_substr($user['name'] ?? '?', 0, 1) }}
                                </span>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $user['name'] }}</p>
                                <p class="truncate text-xs text-slate-500">
                                    @&thinsp;{{ $user['username'] }}
                                    @if (! empty($user['headline'])) · {{ $user['headline'] }} @endif
                                </p>
                            </div>
                        </button>
                    @endforeach
                </div>

                @error('body')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </form>
    @else
        <p class="border-t border-slate-100 pt-3 text-xs text-slate-500 dark:border-slate-800">
            <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:underline">
                Entre
            </a>
            para comentar.
        </p>
    @endauth
</div>
