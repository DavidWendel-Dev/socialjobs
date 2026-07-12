<div class="card" wire:key="post-card-{{ $postId }}" x-data="{ showComments: false }">
    @if (! $post)
        <p class="text-sm text-slate-500">Este post não está mais disponível.</p>
    @else
        {{-- ============================================================
             Header — autor + menu de 3 pontinhos
             ============================================================ --}}
        <div class="flex items-start gap-3">
            @php
                // Se for empresa, linka pro perfil de empresa /c/{slug} e usa o nome da empresa.
                // Se for candidato, linka pro /u/{username} e usa o nome do candidato.
                $isCompanyAuthor = ($post->user?->type ?? 'candidate') === 'company';
                $companySlug     = $isCompanyAuthor ? optional($post->user?->companyProfile)->slug : null;
                $username        = $post->user?->username ?? $post->user?->id;
                $authorUrl       = $isCompanyAuthor && $companySlug
                    ? url('/c/' . $companySlug)
                    : ($username ? url('/u/' . $username) : '#');
                $authorName      = $post->user?->display_name ?? ($post->user?->name ?? 'Usuário');
                $authorHeadline  = $isCompanyAuthor
                    ? (optional($post->user?->companyProfile)->tagline ?? optional($post->user?->companyProfile)->industry ?? 'Empresa')
                    : ($post->user?->headline ?? '');
            @endphp
            <a href="{{ $authorUrl }}" class="shrink-0">
                <x-avatar :user="$post->user" size="md"/>
            </a>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <a href="{{ $authorUrl }}"
                       class="min-w-0 max-w-full truncate font-semibold hover:underline">
                        {{ $authorName }}
                    </a>
                    @if ($isCompanyAuthor)
                        <span class="inline-flex items-center gap-1 rounded-full bg-brand-500/10 px-2 py-0.5 text-[10px] font-bold text-brand-700 dark:text-brand-300">
                            <x-icon name="building" class="h-3 w-3"/> Empresa
                        </span>
                    @else
                        <x-level-badge :user="$post->user"/>
                    @endif
                </div>
                <p class="truncate text-xs text-slate-500">
                    {{ $authorHeadline }}
                    · {{ optional($post->created_at)->diffForHumans() ?? 'agora' }}
                    @if ($post->visibility === 'followers')
                        · <span title="Apenas seguidores">👥</span>
                    @elseif ($post->visibility === 'unlisted')
                        · <span title="Não listado">🔒</span>
                    @endif
                </p>
            </div>

            {{-- Menu dos 3 pontinhos --}}
            <div class="relative" x-data="{ menu: false }" @click.outside="menu = false">
                <button type="button"
                        @click="menu = !menu"
                        class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-ink dark:hover:bg-slate-800 dark:hover:text-white"
                        aria-label="Mais opções">
                    <x-icon name="dots-vertical" class="h-5 w-5"/>
                </button>

                <div x-show="menu"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-cloak
                     class="absolute right-0 top-9 z-20 w-56 rounded-2xl bg-white p-1 shadow-soft-lg ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700">
                    <a href="{{ route('posts.show', $post) }}"
                       class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-800">
                        <x-icon name="arrow-right" class="h-4 w-4"/>
                        Abrir publicação
                    </a>

                    <button type="button"
                            x-data="{
                                copied: false,
                                copy() {
                                    navigator.clipboard.writeText('{{ route('posts.show', $post) }}');
                                    this.copied = true;
                                    setTimeout(() => this.copied = false, 1500);
                                }
                            }"
                            @click="copy(); menu = false"
                            class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-800">
                        <x-icon name="check" class="h-4 w-4"/>
                        <span x-text="copied ? 'Copiado!' : 'Copiar link'"></span>
                    </button>

                    @auth
                        @if (! $isOwner)
                            <button type="button"
                                    @click="menu = false"
                                    class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-800">
                                <x-icon name="x" class="h-4 w-4"/>
                                Denunciar
                            </button>
                        @endif
                    @endauth

                    @if ($isOwner)
                        <hr class="my-1 border-slate-100 dark:border-slate-800">
                        <button type="button"
                                wire:click="delete"
                                wire:confirm="Excluir este post? Esta ação não pode ser desfeita."
                                @click="menu = false"
                                class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">
                            <x-icon name="x" class="h-4 w-4"/>
                            Excluir publicação
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============================================================
             Corpo do post
             ============================================================ --}}
        @php
            $mentionSvc = app(App\Services\MentionService::class);
            $baseUrl    = url('/u/') . '/';
        @endphp
        <div class="prose prose-slate dark:prose-invert mt-3 max-w-none text-sm break-words">
            {!! $mentionSvc->renderHtml($post->body, $baseUrl) !!}
        </div>

        {{-- Badge do tipo de post --}}
        @php
            $postTypeMeta = [
                'article'  => ['label' => 'Artigo',   'icon' => 'book',      'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300'],
                'insight'  => ['label' => 'Insight',  'icon' => 'sparkles',  'class' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300'],
                'question' => ['label' => 'Pergunta', 'icon' => 'message',   'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'],
                'showcase' => ['label' => 'Projeto',  'icon' => 'briefcase', 'class' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300'],
            ];
            $typeMeta = $postTypeMeta[$post->type ?? 'post'] ?? null;
        @endphp
        @if ($typeMeta)
            <div class="mt-2">
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $typeMeta['class'] }}">
                    <x-icon :name="$typeMeta['icon']" class="h-3 w-3"/>
                    {{ $typeMeta['label'] }}
                </span>
            </div>
        @endif

        {{-- Player de música (quando anexada via Deezer) --}}
        @php
            $music = null;
            $lp = $post->link_preview;
            if (is_array($lp) && ($lp['kind'] ?? null) === 'deezer' && ! empty($lp['track']['preview'])) {
                $music = $lp['track'];
            }
        @endphp
        @if ($music)
            <div class="mt-3 flex items-center gap-3 rounded-2xl border border-slate-200 bg-gradient-to-br from-brand-50 to-accent/5 p-3 dark:border-slate-700 dark:from-brand-500/10 dark:to-accent/5"
                 wire:ignore
                 x-data="{
                    playing: false,
                    audio: null,
                    progress: 0,
                    toggle() {
                        try {
                            if (!this.audio) {
                                this.audio = new Audio(@js($music['preview']));
                                this.audio.crossOrigin = 'anonymous';
                                this.audio.addEventListener('timeupdate', () => {
                                    this.progress = (this.audio.currentTime / (this.audio.duration || 30)) * 100;
                                });
                                this.audio.addEventListener('ended', () => { this.playing = false; this.progress = 0; });
                                this.audio.addEventListener('error', (e) => { this.playing = false; console.warn('Erro no áudio Deezer:', e); });
                            }
                            if (this.playing) {
                                this.audio.pause();
                                this.playing = false;
                            } else {
                                const p = this.audio.play();
                                if (p !== undefined) {
                                    p.then(() => { this.playing = true; })
                                     .catch(err => { console.warn('Play bloqueado:', err); });
                                } else {
                                    this.playing = true;
                                }
                            }
                        } catch (e) { console.error(e); }
                    }
                 }"
                 @beforeunload.window="if (audio) audio.pause()">
                @if (! empty($music['cover']))
                    <img src="{{ $music['cover'] }}" alt="" class="h-14 w-14 shrink-0 rounded-lg object-cover shadow-soft">
                @endif

                <button type="button" @click.stop="toggle()"
                        class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-500 text-white shadow-soft transition hover:bg-brand-600">
                    <svg x-show="!playing" class="ml-0.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    <svg x-show="playing" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" x-cloak>
                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                    </svg>
                </button>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold">{{ $music['title'] ?? 'Música' }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $music['artist'] ?? '' }}</p>
                    <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                        <div class="h-1 rounded-full bg-brand-500 transition-all"
                             :style="`width: ${progress}%`"></div>
                    </div>
                </div>

                @if (! empty($music['link']))
                    <a href="{{ $music['link'] }}" target="_blank" rel="noopener"
                       class="hidden shrink-0 text-[10px] font-semibold text-brand-600 hover:underline sm:block"
                       title="Abrir no Deezer">
                        Deezer ↗
                    </a>
                @endif
            </div>
        @endif

        {{-- Mídias (imagens) --}}
        {{-- ============================================================
             Galeria de mídias — layout inspirado em Twitter/Instagram
             1 foto: uma imagem grande
             2 fotos: lado a lado
             3 fotos: 1 grande esquerda + 2 pequenas empilhadas direita
             4 fotos: grid 2x2
             5+ fotos: 2 na primeira linha grandes, 3 na segunda linha (com contador +N)
             ============================================================ --}}
        @if ($post->media->isNotEmpty())
            @php
                $mediaCount = $post->media->count();
                $mediaList  = $post->media->take(4); // mostramos no máx 4 no card
                $extraCount = max(0, $mediaCount - 4);
            @endphp

            <div class="mt-3 overflow-hidden rounded-xl"
                 x-data="{ open: false, current: 0, items: @js($post->media->map(fn($m) => \Illuminate\Support\Facades\Storage::disk('public')->url($m->path))->values()->all()) }">

                @switch($mediaCount)
                    @case(1)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($mediaList[0]->path) }}"
                             alt=""
                             loading="lazy"
                             @click="current = 0; open = true"
                             class="max-h-[520px] w-full cursor-zoom-in object-cover">
                        @break

                    @case(2)
                        <div class="grid grid-cols-2 gap-1">
                            @foreach ($mediaList as $i => $m)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($m->path) }}"
                                     alt="" loading="lazy"
                                     @click="current = {{ $i }}; open = true"
                                     class="h-64 w-full cursor-zoom-in object-cover">
                            @endforeach
                        </div>
                        @break

                    @case(3)
                        <div class="grid grid-cols-2 gap-1" style="grid-template-rows: 1fr 1fr;">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($mediaList[0]->path) }}"
                                 alt="" loading="lazy"
                                 @click="current = 0; open = true"
                                 class="row-span-2 h-full min-h-[300px] w-full cursor-zoom-in object-cover">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($mediaList[1]->path) }}"
                                 alt="" loading="lazy"
                                 @click="current = 1; open = true"
                                 class="h-[148px] w-full cursor-zoom-in object-cover">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($mediaList[2]->path) }}"
                                 alt="" loading="lazy"
                                 @click="current = 2; open = true"
                                 class="h-[148px] w-full cursor-zoom-in object-cover">
                        </div>
                        @break

                    @default
                        <div class="grid grid-cols-2 gap-1">
                            @foreach ($mediaList as $i => $m)
                                <div class="relative">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($m->path) }}"
                                         alt="" loading="lazy"
                                         @click="current = {{ $i }}; open = true"
                                         class="h-40 w-full cursor-zoom-in object-cover sm:h-52">
                                    @if ($i === 3 && $extraCount > 0)
                                        <div @click="current = 3; open = true"
                                             class="absolute inset-0 grid cursor-zoom-in place-items-center bg-black/60 font-display text-2xl font-bold text-white sm:text-3xl">
                                            +{{ $extraCount }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                @endswitch

                {{-- ============================================================
                     Lightbox: overlay em tela cheia com navegação
                     ============================================================ --}}
                <div x-show="open"
                     x-transition.opacity
                     x-cloak
                     @keydown.escape.window="open = false"
                     @keydown.arrow-right.window="current = (current + 1) % items.length"
                     @keydown.arrow-left.window="current = (current - 1 + items.length) % items.length"
                     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4">

                    <button @click="open = false"
                            class="absolute right-4 top-4 grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20"
                            title="Fechar (Esc)">
                        <x-icon name="x" class="h-5 w-5"/>
                    </button>

                    <button @click.stop="current = (current - 1 + items.length) % items.length"
                            x-show="items.length > 1"
                            class="absolute left-4 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20"
                            title="Anterior (←)">
                        ‹
                    </button>

                    <button @click.stop="current = (current + 1) % items.length"
                            x-show="items.length > 1"
                            class="absolute right-4 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20"
                            title="Próxima (→)">
                        ›
                    </button>

                    <img :src="items[current]"
                         alt=""
                         class="max-h-[92vh] max-w-full rounded-lg object-contain shadow-2xl">

                    <div x-show="items.length > 1"
                         class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-white/10 px-3 py-1 text-xs text-white">
                        <span x-text="current + 1"></span> / <span x-text="items.length"></span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Resumo agregado --}}
        @php $viewsCount = (int) ($post->views_count ?? 0); @endphp
        <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
            @if ($totalCount > 0)
                <div class="flex items-center gap-1">
                    <div class="flex -space-x-1">
                        @foreach ($counts as $type => $count)
                            @if ($count > 0 && isset($reactionTypes[$type]))
                                <span title="{{ $reactionTypes[$type]['label'] }}: {{ $count }}"
                                      class="ring-2 ring-white rounded-full dark:ring-slate-900">
                                    <x-reaction-icon :type="$type" size="sm"/>
                                </span>
                            @endif
                        @endforeach
                    </div>
                    <span class="ml-1">{{ $totalCount }}</span>
                </div>
            @else
                <span></span>
            @endif

            <div class="flex items-center gap-3">
                @if ($commentsCount > 0)
                    <button type="button"
                            @click="showComments = !showComments"
                            class="hover:underline">
                        {{ $commentsCount }} {{ $commentsCount === 1 ? 'comentário' : 'comentários' }}
                    </button>
                @endif
                <span class="inline-flex items-center gap-1" title="Visualizações">
                    <x-icon name="eye" class="h-3.5 w-3.5"/>
                    {{ number_format($viewsCount, 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- ============================================================
             Ações
             ============================================================ --}}
        @php
            $myData = $myReaction && isset($reactionTypes[$myReaction])
                ? $reactionTypes[$myReaction]
                : null;
        @endphp
        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2 dark:border-slate-800"
             x-data="{ open: false }"
             @mouseleave="open = false">

            {{-- Botão REAGIR --}}
            <div class="relative flex-1" @mouseenter="open = true">
                <button type="button"
                        wire:click="react('{{ $myReaction ?? 'like' }}')"
                        wire:loading.attr="disabled"
                        wire:target="react"
                        class="flex w-full items-center justify-center gap-2 rounded-xl px-3 py-1.5 text-sm font-medium transition
                               {{ $myData
                                   ? 'font-bold'
                                   : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}"
                        @if ($myData) style="color: {{ $myData['color'] }}" @endif>
                    @if ($myData)
                        <x-reaction-icon :type="$myReaction" size="sm"/>
                        <span>{{ $myData['label'] }}</span>
                    @else
                        <x-icon name="thumb-up" class="h-4 w-4"/>
                        <span>Reagir</span>
                    @endif
                </button>

                <div x-show="open"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1 scale-90"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-cloak
                     @click.outside="open = false"
                     {{--
                        No mobile o botão "Reagir" fica na coluna 1 (esquerda) do card
                        e o popover tem ~340px de largura, então centralizá-lo (left-1/2 + -translate-x-1/2)
                        faz ele estourar para fora da tela do lado esquerdo.
                        - Mobile:  fixo em left-0 (sem translate)
                        - SM+   :  centralizado no botão como antes
                     --}}
                     class="absolute bottom-full left-0 z-20 mb-2 max-w-[calc(100vw-2rem)] overflow-x-auto whitespace-nowrap rounded-full bg-white p-1.5 shadow-soft-lg ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700 sm:left-1/2 sm:-translate-x-1/2 sm:max-w-none sm:overflow-visible">
                    @foreach ($reactionTypes as $key => $data)
                        <button type="button"
                                wire:click="react('{{ $key }}')"
                                wire:target="react"
                                wire:loading.attr="disabled"
                                title="{{ $data['label'] }}"
                                class="group inline-grid h-10 w-10 place-items-center rounded-full transition hover:bg-slate-100 dark:hover:bg-slate-800
                                       {{ $myReaction === $key ? 'ring-2 ring-brand-500' : '' }}">
                            <span class="transition-transform duration-150 group-hover:scale-125">
                                <x-reaction-icon :type="$key" size="lg"/>
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Comentar --}}
            <button type="button"
                    @click="showComments = !showComments"
                    :class="showComments ? 'text-brand-600 font-semibold' : ''"
                    class="btn-ghost text-sm flex-1">
                <x-icon name="message" class="w-4 h-4"/>
                <span>Comentar</span>
            </button>

            {{-- Compartilhar (copia o link) --}}
            <button type="button"
                    x-data="{
                        shared: false,
                        share() {
                            const url = '{{ route('posts.show', $post) }}';
                            if (navigator.share) {
                                navigator.share({ url, title: 'Post SocialJobs' }).catch(() => {});
                            } else {
                                navigator.clipboard.writeText(url);
                            }
                            this.shared = true;
                            setTimeout(() => this.shared = false, 1500);
                        }
                    }"
                    @click="share()"
                    class="btn-ghost text-sm flex-1">
                <x-icon name="arrow-right" class="w-4 h-4"/>
                <span x-text="shared ? 'Copiado!' : 'Compartilhar'"></span>
            </button>
        </div>

        {{-- Comentários inline --}}
        <div x-show="showComments"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-cloak
             class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
            <livewire:feed.comments :post-id="$postId" :key="'comments-'.$postId"/>
        </div>
    @endif
</div>
