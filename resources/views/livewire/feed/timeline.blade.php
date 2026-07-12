<div class="space-y-4">
    {{-- Filtros --}}
    <div class="card !p-2">
        <div class="flex gap-1">
            @foreach ([
                'foryou'    => 'Para você',
                'following' => 'Seguindo',
                'global'    => 'Global',
            ] as $key => $label)
                <button wire:click="setFilter('{{ $key }}')"
                        class="flex-1 rounded-xl px-4 py-2 text-sm font-medium transition
                               {{ $filter === $key ? 'bg-brand-500 text-white shadow-soft' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Composer --}}
    <livewire:feed.composer />

    {{-- Posts --}}
    <div class="space-y-4" wire:scroll>
        @forelse ($posts as $post)
            <livewire:feed.post-card :post-id="$post->id" :key="'post-'.$post->id" />
        @empty
            <div class="card text-center text-slate-500">
                {{-- Ícone: balão de notícias/megafone --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-2 h-10 w-10 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5m-7.5 3h4.5m-4.5 3h7.5m-7.5 3h4.5M3.75 21V4.5a1.5 1.5 0 0 1 1.5-1.5h13.5a1.5 1.5 0 0 1 1.5 1.5V21l-3.75-1.5L12 21l-4.5-1.5L3.75 21Z"/>
                </svg>
                <p class="font-medium">Seu feed está esperando conteúdo!</p>
                <p class="text-sm flex items-center justify-center gap-1.5">
                    Siga pessoas ou publique o primeiro post
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"/>
                    </svg>
                </p>
            </div>
        @endforelse

        @if (method_exists($posts, 'hasMorePages') && $posts->hasMorePages())
            <div class="pt-2 text-center">
                <button wire:click="$refresh" class="btn-secondary">Carregar mais</button>
            </div>
        @endif
    </div>
</div>
