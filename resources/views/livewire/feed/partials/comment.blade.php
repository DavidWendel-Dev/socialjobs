@php
    /** @var \App\Models\Comment $comment */
    $depth      = $depth ?? 0;
    $maxDepth   = 3;
    $isMine     = auth()->check() && auth()->id() === $comment->user_id;
    $username   = $comment->user?->username ?? $comment->user?->id;
    $mentionSvc = app(App\Services\MentionService::class);
    $baseUrl    = url('/u/') . '/';
@endphp

<li class="mt-3 first:mt-0" wire:key="comment-{{ $comment->id }}">
    <div class="flex gap-2.5">
        {{-- Avatar --}}
        <a href="{{ $username ? url('/u/' . $username) : '#' }}" class="shrink-0" wire:navigate>
            <x-avatar :user="$comment->user" size="{{ $depth === 0 ? 'sm' : 'sm' }}"/>
        </a>

        {{-- Balão + ações --}}
        <div class="min-w-0 flex-1">
            <div class="group relative rounded-2xl bg-slate-100 px-3 py-2 dark:bg-slate-800">
                <div class="flex items-baseline justify-between gap-2">
                    <a href="{{ $username ? url('/u/' . $username) : '#' }}"
                       wire:navigate
                       class="truncate text-xs font-semibold hover:underline">
                        {{ $comment->user?->name ?? 'Usuário' }}
                    </a>
                    @if ($isMine)
                        <button type="button"
                                wire:click="remove({{ $comment->id }})"
                                wire:confirm="Excluir este comentário?"
                                class="text-[11px] text-slate-400 opacity-0 transition hover:text-rose-600 group-hover:opacity-100"
                                title="Excluir">
                            Excluir
                        </button>
                    @endif
                </div>
                <p class="mt-0.5 whitespace-pre-line break-words text-sm text-slate-800 dark:text-slate-100">
                    {!! $mentionSvc->renderHtml($comment->body, $baseUrl) !!}
                </p>
            </div>

            {{-- Rodapé: timestamp + botão responder --}}
            <div class="mt-0.5 flex items-center gap-3 pl-3 text-[10px] text-slate-400">
                <span>{{ optional($comment->created_at)->diffForHumans() }}</span>
                @auth
                    @if ($depth < $maxDepth)
                        <button type="button"
                                wire:click="setReply({{ $comment->id }})"
                                class="font-semibold text-brand-600 hover:underline">
                            Responder
                        </button>
                    @endif
                @endauth
            </div>

            {{-- Respostas (recursivo) --}}
            @if ($comment->replies && $comment->replies->count())
                @php
                    // Indentação diminui em telas pequenas para não estourar
                    $indent = min($depth + 1, $maxDepth);
                    $marginClass = ['ml-0','ml-4 sm:ml-6', 'ml-4 sm:ml-8', 'ml-4 sm:ml-8'][$indent] ?? 'ml-4';
                @endphp
                <ul class="{{ $marginClass }} mt-1 border-l-2 border-slate-100 pl-3 dark:border-slate-800">
                    @foreach ($comment->replies as $reply)
                        @include('livewire.feed.partials.comment', [
                            'comment' => $reply,
                            'depth'   => $depth + 1,
                        ])
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</li>
