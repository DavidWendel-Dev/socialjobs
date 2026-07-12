<div class="card"
     x-data="postComposer"
     x-init="localBody = $wire.body || ''">
    <form wire:submit.prevent="save" class="space-y-3">

        {{-- ============================================================
             Cabeçalho: avatar + textarea + contador
             ============================================================ --}}
        <div class="flex gap-3">
            <x-avatar :user="auth()->user()" size="md"/>
            <div class="relative min-w-0 flex-1">
                <textarea x-model="localBody"
                          x-ref="postBody"
                          @input="onInput($event); $wire.set('body', localBody)"
                          @keydown.escape="showMentions = false; $wire.closeMentions()"
                          rows="2"
                          maxlength="5000"
                          placeholder="Compartilhe algo... use @ para mencionar alguém"
                          class="input resize-none !border-0 !bg-transparent !p-0 !ring-0 focus:!ring-0 min-h-[52px]"></textarea>

                {{-- Dropdown de menções ancorado abaixo do textarea --}}
                <div x-show="showMentions && $wire.mentionResults && $wire.mentionResults.length > 0"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-cloak
                     class="absolute left-0 right-0 top-full z-20 mt-2 max-h-64 overflow-y-auto rounded-2xl bg-white p-1 shadow-soft-lg ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700">
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

                @error('body') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror

                <p class="mt-1 text-right text-[10px] text-slate-400"
                   :class="localBody.length > 4500 && 'text-amber-600'"
                   x-text="`${localBody.length}/5000`"></p>
            </div>
        </div>

        {{-- ============================================================
             Preview: imagens anexadas (até 6)
             Grid responsivo — 1 imagem grande, 2+ imagens em grade
             ============================================================ --}}
        {{-- ============================================================
             Preview: imagens anexadas (até 6)
             Grid responsivo — 1 imagem grande, 2+ imagens em grade
             Container ÚNICO envolvendo tudo (@if/@else quebra o morph do
             Livewire quando $images passa de 0 → 1).
             ============================================================ --}}
        <div wire:key="composer-images-wrapper" class="composer-images-wrapper">
            @if (count($images) > 0)
                <div class="grid gap-2 {{ count($images) === 1 ? 'grid-cols-1' : 'grid-cols-2 sm:grid-cols-3' }}">
                    @foreach ($images as $index => $img)
                        <div class="relative overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800"
                             wire:key="composer-image-{{ $img->getFilename() }}">
                            <img src="{{ $img->temporaryUrl() }}"
                                 alt="Imagem {{ $index + 1 }}"
                                 class="h-40 w-full object-cover {{ count($images) === 1 ? 'sm:h-64' : '' }}">
                            <button type="button"
                                    wire:click="removeImage({{ $index }})"
                                    class="absolute right-2 top-2 grid h-7 w-7 place-items-center rounded-full bg-black/60 text-white transition hover:bg-rose-600"
                                    title="Remover imagem">
                                <x-icon name="x" class="h-3.5 w-3.5"/>
                            </button>
                        </div>
                    @endforeach

                    {{-- Loader durante upload de novas imagens (quando já tem outras) --}}
                    <div wire:loading wire:target="newImages"
                         class="grid h-40 place-items-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800">
                        <div class="flex flex-col items-center gap-1">
                            <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".3"/>
                                <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            <span class="text-[10px]">Enviando...</span>
                        </div>
                    </div>
                </div>
            @else
                {{-- Loader quando NÃO tem imagens ainda mas o upload iniciou --}}
                <div wire:loading wire:target="newImages"
                     class="grid h-40 place-items-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800">
                    <div class="flex flex-col items-center gap-1">
                        <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".3"/>
                            <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        <span class="text-[10px]">Preparando imagens...</span>
                    </div>
                </div>
            @endif
        </div>
        @error('images') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
        @error('images.*') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror

        {{-- ============================================================
             Preview: música do Deezer anexada
             ============================================================ --}}
        @if ($selectedMusic)
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-gradient-to-br from-brand-50 to-accent/5 p-3 dark:border-slate-700 dark:from-brand-500/10 dark:to-accent/5"
                 wire:ignore
                 x-data="{
                    playing: false,
                    audio: null,
                    toggle() {
                        try {
                            if (!this.audio) {
                                this.audio = new Audio(@js($selectedMusic['preview']));
                                this.audio.crossOrigin = 'anonymous';
                                this.audio.addEventListener('ended', () => this.playing = false);
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
                 }">
                @if ($selectedMusic['cover'])
                    <img src="{{ $selectedMusic['cover'] }}" alt=""
                         class="h-14 w-14 rounded-lg object-cover shadow-soft">
                @endif

                <button type="button" @click.stop="toggle()"
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-500 text-white shadow-soft transition hover:bg-brand-600">
                    <svg x-show="!playing" class="h-4 w-4 ml-0.5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    <svg x-show="playing" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" x-cloak>
                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                    </svg>
                </button>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold">{{ $selectedMusic['title'] }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $selectedMusic['artist'] }}</p>
                    <p class="text-[10px] text-slate-400">Prévia de 30s · Deezer</p>
                </div>

                <button type="button"
                        wire:click="removeMusic"
                        @click="if (audio) { audio.pause(); playing = false; }"
                        class="grid h-7 w-7 shrink-0 place-items-center rounded-full text-slate-400 hover:bg-rose-100 hover:text-rose-600"
                        title="Remover música">
                    <x-icon name="x" class="h-3.5 w-3.5"/>
                </button>
            </div>
        @endif

        {{-- ============================================================
             Painel de busca de música do Deezer
             ============================================================ --}}
        <div x-show="showMusicPanel"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-cloak
             class="rounded-2xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/50">

            <div class="mb-2 flex items-center justify-between">
                <p class="flex items-center gap-2 text-sm font-semibold">
                    <span class="grid h-6 w-6 place-items-center rounded-md bg-brand-500 text-white text-xs">♪</span>
                    Adicionar música do Deezer
                </p>
                <button type="button" @click="showMusicPanel = false"
                        class="text-xs text-slate-400 hover:text-ink">Fechar</button>
            </div>

            <div class="relative">
                <x-icon name="search"
                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"/>
                <input type="text"
                       wire:model.live.debounce.450ms="musicQuery"
                       placeholder="Nome da música, artista ou álbum..."
                       autocomplete="off"
                       class="w-full rounded-full border-slate-200 bg-white pl-10 pr-4 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-900">
            </div>

            <div wire:loading.flex wire:target="musicQuery" class="mt-2 items-center gap-2 text-xs text-slate-500">
                <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".3"/>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                </svg>
                Buscando no Deezer...
            </div>

            <div wire:loading.remove wire:target="musicQuery" class="mt-2 space-y-1">
                @if (count($musicResults))
                    <ul class="max-h-64 overflow-y-auto rounded-xl bg-white p-1 dark:bg-slate-900"
                        wire:ignore.self
                        x-data="{
                            currentAudio: null,
                            currentId: null,
                            play(id, url) {
                                // Se já está tocando o mesmo, pausa
                                if (this.currentId === id && this.currentAudio && !this.currentAudio.paused) {
                                    this.currentAudio.pause();
                                    this.currentId = null;
                                    return;
                                }
                                // Pausa qualquer prévia anterior
                                if (this.currentAudio) {
                                    try { this.currentAudio.pause(); } catch (e) {}
                                }
                                // Cria e toca a nova
                                const a = new Audio(url);
                                a.crossOrigin = 'anonymous';
                                a.volume = 0.9;
                                a.addEventListener('ended', () => { this.currentId = null; });
                                a.addEventListener('error', () => { this.currentId = null; });
                                const p = a.play();
                                if (p) {
                                    p.then(() => {
                                        this.currentAudio = a;
                                        this.currentId = id;
                                    }).catch(err => {
                                        console.warn('Play bloqueado:', err);
                                    });
                                } else {
                                    this.currentAudio = a;
                                    this.currentId = id;
                                }
                            }
                        }"
                        @remove-music-preview.window="if (currentAudio) { currentAudio.pause(); currentId = null; }">
                        @foreach ($musicResults as $track)
                            <li wire:key="track-{{ $track['id'] }}">
                                <div class="group flex w-full items-center gap-2.5 rounded-lg p-2 transition hover:bg-brand-50 dark:hover:bg-brand-500/10">
                                    {{-- Capa com play overlay --}}
                                    <div class="relative h-10 w-10 shrink-0">
                                        @if ($track['cover'])
                                            <img src="{{ $track['cover'] }}" alt=""
                                                 class="h-10 w-10 rounded object-cover">
                                        @else
                                            <div class="grid h-10 w-10 place-items-center rounded bg-slate-200 text-lg">🎵</div>
                                        @endif

                                        {{-- Botão de preview: sobrepõe a capa --}}
                                        <button type="button"
                                                @click.stop="play({{ (int) $track['id'] }}, @js($track['preview']))"
                                                class="absolute inset-0 grid place-items-center rounded bg-black/50 text-white opacity-0 transition group-hover:opacity-100"
                                                :class="currentId === {{ (int) $track['id'] }} && 'opacity-100 !bg-brand-500/70'"
                                                title="Ouvir prévia">
                                            {{-- Ícone Play --}}
                                            <svg x-show="currentId !== {{ (int) $track['id'] }}"
                                                 class="ml-0.5 h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                            {{-- Ícone Pause --}}
                                            <svg x-show="currentId === {{ (int) $track['id'] }}"
                                                 class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" x-cloak>
                                                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Título + artista + indicador tocando --}}
                                    <button type="button"
                                            wire:click="attachMusicById({{ (int) $track['id'] }})"
                                            wire:loading.attr="disabled"
                                            class="min-w-0 flex-1 text-left focus:outline-none">
                                        <p class="truncate text-sm font-medium"
                                           :class="currentId === {{ (int) $track['id'] }} && 'text-brand-600 dark:text-brand-400'">
                                            {{ $track['title'] }}
                                            <span x-show="currentId === {{ (int) $track['id'] }}"
                                                  class="ml-1 inline-flex items-center gap-0.5 text-brand-500"
                                                  x-cloak>
                                                <span class="inline-block h-2.5 w-0.5 animate-pulse bg-current"></span>
                                                <span class="inline-block h-2 w-0.5 animate-pulse bg-current" style="animation-delay:.1s"></span>
                                                <span class="inline-block h-3 w-0.5 animate-pulse bg-current" style="animation-delay:.2s"></span>
                                            </span>
                                        </p>
                                        <p class="truncate text-xs text-slate-500">{{ $track['artist'] }}</p>
                                    </button>

                                    {{-- Duração + botão selecionar --}}
                                    <span class="hidden shrink-0 text-[10px] text-slate-400 sm:inline">
                                        {{ gmdate('i:s', $track['duration']) }}
                                    </span>
                                    <button type="button"
                                            wire:click="attachMusicById({{ (int) $track['id'] }})"
                                            wire:loading.attr="disabled"
                                            class="shrink-0 rounded-full bg-brand-500 px-2.5 py-1 text-[10px] font-bold text-white transition hover:bg-brand-600"
                                            title="Anexar ao post">
                                        + Adicionar
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @elseif (mb_strlen($musicQuery) >= 2)
                    <p class="rounded-xl bg-white p-3 text-center text-xs text-slate-500 dark:bg-slate-900">
                        Nada encontrado para "{{ $musicQuery }}"
                    </p>
                @else
                    <p class="rounded-xl bg-white p-3 text-center text-xs text-slate-500 dark:bg-slate-900">
                        Digite pelo menos 2 caracteres para buscar
                    </p>
                @endif
            </div>
        </div>

        {{-- ============================================================
             Seletor de tipo de post
             ============================================================ --}}
        <div class="flex flex-nowrap overflow-x-auto scrollbar-none sm:flex-wrap items-center gap-1.5 border-t border-slate-100 pt-3 dark:border-slate-800 -mx-4 sm:mx-0 px-4 sm:px-0">
            <span class="mr-1 flex-shrink-0 text-[10px] font-bold uppercase tracking-wider text-slate-400">Tipo:</span>
            @foreach ($postTypes as $key => $meta)
                @php
                    $active = $type === $key;
                    $colorMap = [
                        'slate'  => ['on' => 'bg-slate-900 text-white',       'off' => 'text-slate-600'],
                        'blue'   => ['on' => 'bg-blue-500 text-white',        'off' => 'text-blue-700'],
                        'brand'  => ['on' => 'bg-brand-500 text-white',       'off' => 'text-brand-700'],
                        'amber'  => ['on' => 'bg-amber-500 text-white',       'off' => 'text-amber-700'],
                        'accent' => ['on' => 'bg-accent text-white',          'off' => 'text-accent'],
                    ];
                    $classes = $active ? $colorMap[$meta['color']]['on'] : 'hover:bg-slate-100 dark:hover:bg-slate-800 ' . $colorMap[$meta['color']]['off'];
                @endphp
                <button type="button"
                        wire:click="setType('{{ $key }}')"
                        class="inline-flex flex-shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium whitespace-nowrap transition {{ $classes }}">
                    <x-icon :name="$meta['icon']" class="h-3 w-3"/>
                    {{ $meta['label'] }}
                </button>
            @endforeach

            {{-- Toggle "Destaque no CV" — marca o post para aparecer no Currículo Digital --}}
            <button type="button"
                    wire:click="$toggle('isFeatured')"
                    class="ml-auto flex-shrink-0 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium whitespace-nowrap transition
                           {{ $isFeatured
                               ? 'bg-brand-500 text-white'
                               : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                    title="{{ $isFeatured ? 'Vai aparecer no seu Currículo Digital' : 'Marcar como destaque no CV' }}">
                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2 4 5v7c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V5l-8-3z" opacity="{{ $isFeatured ? '1' : '.5' }}"/>
                </svg>
                <span class="hidden sm:inline">Destaque</span>
            </button>
        </div>

        {{-- ============================================================
             Rodapé: anexos + botão publicar
             ============================================================ --}}
        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
            <div class="flex flex-wrap items-center gap-1">
                {{-- Anexar imagens (múltiplas — até 6). Usamos $newImages como buffer:
                     o backend faz o merge em $images para permitir múltiplas seleções. --}}
                @php $canAddMore = count($images) < 6; @endphp
                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 {{ $canAddMore ? '' : 'pointer-events-none opacity-40' }}"
                       title="Anexar imagens (máx 6)">
                    <input type="file"
                           wire:model="newImages"
                           accept="image/*"
                           multiple
                           @disabled(! $canAddMore)
                           class="hidden">
                    <x-icon name="camera" class="h-4 w-4"/>
                    <span class="hidden sm:inline">
                        Foto{{ count($images) > 0 ? ' (' . count($images) . '/6)' : 's' }}
                    </span>
                    <span class="sm:hidden">
                        {{ count($images) > 0 ? count($images) . '/6' : '' }}
                    </span>
                </label>

                {{-- Anexar música --}}
                <button type="button"
                        @click="showMusicPanel = !showMusicPanel"
                        :class="showMusicPanel && 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300'"
                        class="inline-flex items-center gap-1.5 rounded-xl px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                        title="Anexar música do Deezer">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18V5l12-2v13"/>
                        <circle cx="6" cy="18" r="3"/>
                        <circle cx="18" cy="16" r="3"/>
                    </svg>
                    <span class="hidden sm:inline">Música</span>
                </button>
            </div>

            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save,newImages"
                    :disabled="localBody.trim() === ''"
                    :class="localBody.trim() === '' ? 'opacity-50 cursor-not-allowed' : ''"
                    class="btn-primary">
                <span wire:loading.remove wire:target="save">Publicar</span>
                <span wire:loading wire:target="save">Enviando...</span>
            </button>
        </div>
    </form>
</div>
