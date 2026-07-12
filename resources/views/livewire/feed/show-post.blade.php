<div class="mx-auto max-w-3xl space-y-4">

    {{-- ============================================================
         Header — Voltar + breadcrumb
         ============================================================ --}}
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('feed') }}" wire:navigate
           class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
            <x-icon name="arrow-right" class="h-4 w-4 rotate-180"/>
            <span>Voltar ao feed</span>
        </a>

        @if ($post)
            <button type="button"
                    x-data
                    @click="navigator.clipboard.writeText('{{ route('posts.show', $post) }}'); $dispatch('toast', { msg: 'Link copiado!' })"
                    class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                <x-icon name="sparkles" class="h-4 w-4"/>
                <span class="hidden sm:inline">Copiar link</span>
            </button>
        @endif
    </div>

    @if (! $post)
        {{-- ============================================================
             Post inexistente ou deletado
             ============================================================ --}}
        <div class="card text-center py-12">
            <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                <x-icon name="x" class="h-7 w-7"/>
            </div>
            <h1 class="mt-4 font-display text-xl font-bold text-slate-900 dark:text-white">
                Post não encontrado
            </h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Ele pode ter sido removido pelo autor ou o link está incorreto.
            </p>
            <a href="{{ route('feed') }}" wire:navigate class="btn-primary mt-6 inline-flex text-sm">
                Voltar ao feed
            </a>
        </div>
    @else
        {{-- ============================================================
             Meta info do post
             ============================================================ --}}
        @php
            $authorName = $post->user?->name ?? 'Alguém';
            $viewsCount = (int) ($post->views_count ?? 0);
        @endphp

        <div class="rounded-2xl bg-gradient-to-br from-brand-500/10 via-brand-500/5 to-transparent p-4 sm:px-5 sm:py-4 ring-1 ring-brand-500/20 dark:from-brand-500/15 dark:via-brand-500/10">
            <div class="flex items-start gap-3">
                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-500/15 text-brand-600 dark:text-brand-400">
                    <x-icon name="chat" class="h-5 w-5"/>
                </div>
                <div class="min-w-0 flex-1">
                    <h1 class="font-display text-base font-bold text-slate-900 dark:text-white sm:text-lg">
                        Publicação de {{ $authorName }}
                    </h1>
                    <p class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1">
                            <x-icon name="eye" class="h-3.5 w-3.5"/>
                            {{ number_format($viewsCount, 0, ',', '.') }} {{ $viewsCount === 1 ? 'visualização' : 'visualizações' }}
                        </span>
                        <span>Publicado {{ optional($post->created_at)->diffForHumans() ?? 'agora' }}</span>
                        @if ($post->visibility === 'followers')
                            <span class="inline-flex items-center gap-1">
                                <x-icon name="users" class="h-3.5 w-3.5"/>
                                Apenas seguidores
                            </span>
                        @elseif ($post->visibility === 'unlisted')
                            <span class="inline-flex items-center gap-1">
                                <x-icon name="no-symbol" class="h-3.5 w-3.5"/>
                                Não listado
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- ============================================================
             O card completo do post (com reações, mídia, três-pontinhos)
             ============================================================ --}}
        <livewire:feed.post-card :post-id="$post->id" :wire:key="'show-post-' . $post->id"/>

        {{-- ============================================================
             Comentários
             ============================================================ --}}
        <div class="card">
            <div class="mb-3 flex items-center gap-2">
                <div class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-400">
                    <x-icon name="message" class="h-4 w-4"/>
                </div>
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-white">
                    Comentários
                </h2>
            </div>
            <livewire:feed.comments :postId="$post->id" :wire:key="'comments-' . $post->id"/>
        </div>

        {{-- ============================================================
             Mais posts do autor (até 3)
             ============================================================ --}}
        @if ($morePosts->isNotEmpty())
            @php $authorUsername = $post->user?->username ?? $post->user?->id; @endphp
            <div class="card">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-400">
                            <x-icon name="sparkles" class="h-4 w-4"/>
                        </div>
                        <h2 class="truncate font-display text-base font-bold text-slate-900 dark:text-white">
                            Mais de {{ $authorName }}
                        </h2>
                    </div>
                    @if ($authorUsername)
                        <a href="{{ url('/u/' . $authorUsername) }}" wire:navigate
                           class="shrink-0 text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">
                            Ver perfil →
                        </a>
                    @endif
                </div>

                <div class="space-y-2">
                    @foreach ($morePosts as $mp)
                        <a href="{{ route('posts.show', $mp) }}" wire:navigate
                           class="group block rounded-xl border border-slate-100 bg-slate-50 p-3 transition hover:border-brand-300 hover:bg-white dark:border-slate-800 dark:bg-slate-800/40 dark:hover:border-brand-500/50 dark:hover:bg-slate-800">
                            <p class="line-clamp-2 text-sm text-slate-700 group-hover:text-slate-900 dark:text-slate-300 dark:group-hover:text-white">
                                {{ \Illuminate\Support\Str::limit(strip_tags((string) $mp->body), 140) }}
                            </p>
                            <div class="mt-2 flex items-center gap-3 text-[11px] text-slate-500">
                                <span>{{ optional($mp->created_at)->diffForHumans() ?? 'agora' }}</span>
                                <span class="inline-flex items-center gap-1">
                                    <x-icon name="eye" class="h-3 w-3"/>
                                    {{ number_format((int) ($mp->views_count ?? 0), 0, ',', '.') }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
