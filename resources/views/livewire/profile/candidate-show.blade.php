<div class="space-y-4">
    {{-- ============================================================
         CABEÇALHO — Compacto, responsivo, sem overflow
         ============================================================ --}}
    <section class="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
        {{-- ============================================================
             CAPA — clicável para trocar quando é o dono do perfil
             ============================================================ --}}
        <div class="group relative">
            @if ($user?->cover_path)
                <div class="h-28 sm:h-40"
                     style="background-image:url('{{ $user->cover_url }}');background-size:cover;background-position:center"></div>
            @else
                <div class="relative h-28 bg-gradient-to-br from-brand-500 to-accent sm:h-40">
                    <div class="absolute inset-0 opacity-25"
                         style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,.5) 0, transparent 40%), radial-gradient(circle at 80% 60%, rgba(255,255,255,.3) 0, transparent 40%);"></div>
                </div>
            @endif

            @if ($isOwner)
                {{-- Overlay para trocar capa. No mobile fica sempre visível; no desktop só no hover. --}}
                <label for="coverUpload"
                       class="absolute inset-0 flex cursor-pointer items-center justify-center bg-black/40 opacity-100 transition sm:opacity-0 sm:group-hover:opacity-100"
                       wire:loading.class="!opacity-100"
                       wire:target="coverUpload,updatedCoverUpload">
                    <div wire:loading.remove wire:target="coverUpload,updatedCoverUpload"
                         class="flex flex-col items-center gap-1 text-white">
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-white/20 backdrop-blur">
                            <x-icon name="camera" class="h-5 w-5"/>
                        </span>
                        <span class="text-xs font-medium">
                            {{ $user?->cover_path ? 'Trocar capa' : 'Adicionar capa' }}
                        </span>
                    </div>
                    <div wire:loading wire:target="coverUpload,updatedCoverUpload"
                         class="flex items-center gap-2 text-white">
                        <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".3"/>
                            <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        <span class="text-xs">Enviando...</span>
                    </div>
                    <input type="file" id="coverUpload" wire:model="coverUpload"
                           accept="image/jpeg,image/png,image/webp" class="sr-only">
                </label>

                @if ($user?->cover_path)
                    {{-- Botão remover capa (canto superior direito) --}}
                    <button type="button"
                            wire:click="removeCover"
                            wire:confirm="Remover a capa atual?"
                            class="absolute right-2 top-2 z-10 grid h-8 w-8 place-items-center rounded-full bg-black/60 text-white opacity-100 transition hover:bg-rose-600 sm:opacity-0 sm:group-hover:opacity-100"
                            title="Remover capa">
                        <x-icon name="trash" class="h-4 w-4"/>
                    </button>
                @endif
            @endif
        </div>

        @error('coverUpload')
            <p class="border-b border-rose-200 bg-rose-50 px-4 py-2 text-xs text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                {{ $message }}
            </p>
        @enderror

        {{-- Corpo do cabeçalho --}}
        <div class="px-4 pb-4 sm:px-6 sm:pb-5">
            {{-- Linha 1: avatar + identidade + ações (desktop) --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:gap-5">
                {{-- ================ Avatar (com editar) ================ --}}
                <div class="relative -mt-12 shrink-0 sm:-mt-14">
                    <div class="group/avatar relative">
                        <x-avatar :user="$user" size="lg"
                                  class="!h-24 !w-24 !text-2xl ring-4 ring-white shadow-soft dark:ring-slate-900 sm:!h-28 sm:!w-28 sm:!text-3xl"/>

                        @if ($user?->open_to_work && ! $isOwner)
                            <span class="absolute -bottom-0.5 -right-0.5 grid h-7 w-7 place-items-center rounded-full bg-brand-500 ring-4 ring-white dark:ring-slate-900"
                                  title="Aberto a oportunidades">
                                <x-icon name="briefcase" class="h-3.5 w-3.5 text-white"/>
                            </span>
                        @endif

                        @if ($isOwner)
                            {{-- Overlay para trocar avatar (desktop hover) --}}
                            <label for="avatarUpload"
                                   class="absolute inset-0 hidden cursor-pointer items-center justify-center rounded-full bg-black/50 text-white opacity-0 transition group-hover/avatar:opacity-100 sm:flex"
                                   wire:loading.class="!opacity-100"
                                   wire:target="avatarUpload,updatedAvatarUpload">
                                <span wire:loading.remove wire:target="avatarUpload,updatedAvatarUpload">
                                    <x-icon name="camera" class="h-6 w-6"/>
                                </span>
                                <svg wire:loading wire:target="avatarUpload,updatedAvatarUpload"
                                     class="h-6 w-6 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".3"/>
                                    <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                </svg>
                                <input type="file" id="avatarUpload" wire:model="avatarUpload"
                                       accept="image/jpeg,image/png,image/webp" class="sr-only">
                            </label>

                            {{-- Micro-botão flutuante (sempre visível — funciona em mobile e desktop) --}}
                            <label for="avatarUpload"
                                   class="absolute -bottom-0.5 -right-0.5 grid h-8 w-8 cursor-pointer place-items-center rounded-full bg-brand-500 text-white shadow-soft ring-4 ring-white transition hover:bg-brand-600 dark:ring-slate-900 sm:hidden"
                                   title="Trocar foto de perfil">
                                <x-icon name="camera" class="h-4 w-4"/>
                                <input type="file" wire:model="avatarUpload"
                                       accept="image/jpeg,image/png,image/webp" class="sr-only">
                            </label>
                        @endif
                    </div>
                </div>

                {{-- Identidade --}}
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <h1 class="font-display text-xl font-bold leading-tight tracking-tight sm:text-2xl line-clamp-2 break-words">
                            {{ $user?->name ?? 'Candidato' }}
                        </h1>
                        @if ($user)
                            <x-level-badge :user="$user"/>
                        @endif
                    </div>

                    @if ($user?->username)
                        <p class="mt-0.5 truncate font-mono text-xs text-slate-400">&#64;{{ $user->username }}</p>
                    @endif

                    @if ($user?->headline)
                        <p class="mt-1 text-sm text-slate-700 dark:text-slate-300 line-clamp-2">
                            {{ $user->headline }}
                        </p>
                    @endif

                    {{-- Meta: localização + status --}}
                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                        @if ($user?->location)
                            <span class="inline-flex items-center gap-1 truncate">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                                     stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 shrink-0">
                                    <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0Z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span class="truncate">{{ $user->location }}</span>
                            </span>
                        @endif
                        @if ($user?->open_to_work)
                            <span class="inline-flex items-center gap-1 rounded-full bg-brand-500/10 px-2 py-0.5 font-medium text-brand-700 dark:text-brand-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                                Aberto a oportunidades
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Ações (desktop) --}}
                <div class="hidden shrink-0 items-center gap-2 sm:flex">
                    @if ($isOwner)
                        @if ($user?->avatar_path)
                            <button type="button"
                                    wire:click="removeAvatar"
                                    wire:confirm="Remover sua foto de perfil?"
                                    class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-rose-600 dark:border-slate-700 dark:hover:bg-slate-800"
                                    title="Remover foto de perfil">
                                <x-icon name="trash" class="h-4 w-4"/>
                            </button>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="btn-primary">
                            <x-icon name="pencil" class="mr-1.5 h-4 w-4"/> Editar
                        </a>
                    @else
                        @if ($isFollowing)
                            <button wire:click="unfollow" class="btn-secondary">
                                <x-icon name="check" class="mr-1.5 h-4 w-4"/> Seguindo
                            </button>
                        @else
                            <button wire:click="follow" class="btn-primary" @if($isBlocked) disabled @endif>
                                <x-icon name="plus" class="mr-1.5 h-4 w-4"/> Seguir
                            </button>
                        @endif
                        <a href="{{ route('messages.index', ['user' => $user->id]) }}" class="btn-secondary" title="Mensagem">
                            <x-icon name="message" class="h-4 w-4"/>
                        </a>

                        {{-- Menu de 3 pontinhos: bloquear/desbloquear --}}
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button type="button" @click="open = !open" class="btn-secondary" title="Mais opções">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="12" cy="5" r="1.75"/><circle cx="12" cy="12" r="1.75"/><circle cx="12" cy="19" r="1.75"/>
                                </svg>
                            </button>
                            <div x-show="open" x-transition x-cloak
                                 class="absolute right-0 top-full z-20 mt-1 w-48 overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                                @if ($isBlocked)
                                    <button type="button" wire:click="unblockUser" @click="open = false"
                                            class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10">
                                        <x-icon name="check" class="h-4 w-4"/> Desbloquear
                                    </button>
                                @else
                                    <button type="button" wire:click="blockUser"
                                            wire:confirm="Bloquear {{ $user->name }}? Vocês não poderão trocar mensagens nem ver posts um do outro."
                                            @click="open = false"
                                            class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">
                                        <x-icon name="x" class="h-4 w-4"/> Bloquear usuário
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @error('avatarUpload')
                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
            @enderror

            @if (session('status'))
                <div class="mt-3 rounded-xl bg-brand-50 px-3 py-2 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Ações (mobile — full-width, empilhadas) --}}
            <div class="mt-4 flex gap-2 sm:hidden">
                @if ($isOwner)
                    <a href="{{ route('profile.edit') }}" class="btn-primary flex-1 justify-center">
                        <x-icon name="pencil" class="mr-1.5 h-4 w-4"/> Editar perfil
                    </a>
                @else
                    @if ($isFollowing)
                        <button wire:click="unfollow" class="btn-secondary flex-1 justify-center">
                            <x-icon name="check" class="mr-1.5 h-4 w-4"/> Seguindo
                        </button>
                    @else
                        <button wire:click="follow" class="btn-primary flex-1 justify-center">
                            <x-icon name="plus" class="mr-1.5 h-4 w-4"/> Seguir
                        </button>
                    @endif
                    <a href="{{ route('messages.index', ['user' => $user->id]) }}" class="btn-secondary flex-1 justify-center">
                        <x-icon name="message" class="mr-1.5 h-4 w-4"/> Mensagem
                    </a>
                @endif
            </div>

            {{-- Visualizações do perfil (só o dono vê) --}}
            @if ($isOwner)
                @php $pv = (int) ($user?->profile_views_count ?? 0); @endphp
                <div class="mt-3 flex items-center gap-1.5 rounded-xl bg-slate-50 px-3 py-2 text-xs text-slate-600 dark:bg-slate-800/60 dark:text-slate-300">
                    <x-icon name="eye" class="h-4 w-4 text-brand-500"/>
                    <span>
                        <strong class="font-bold text-slate-900 dark:text-white">{{ number_format($pv, 0, ',', '.') }}</strong>
                        {{ $pv === 1 ? 'visualização do seu perfil' : 'visualizações do seu perfil' }}
                    </span>
                </div>
            @endif

            {{-- Métricas — inline, sem quebra, compactas --}}
            <dl class="mt-4 flex items-center justify-between gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                <div class="min-w-0 flex-1 text-center">
                    <dd class="font-display text-base font-bold sm:text-lg">{{ $this->formatCount($followersCount) }}</dd>
                    <dt class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-500 sm:text-[11px]">Seguidores</dt>
                </div>
                <span class="h-8 w-px shrink-0 bg-slate-100 dark:bg-slate-800"></span>
                <div class="min-w-0 flex-1 text-center">
                    <dd class="font-display text-base font-bold sm:text-lg">{{ $this->formatCount($followingCount) }}</dd>
                    <dt class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-500 sm:text-[11px]">Seguindo</dt>
                </div>
                <span class="h-8 w-px shrink-0 bg-slate-100 dark:bg-slate-800"></span>
                <div class="min-w-0 flex-1 text-center">
                    <dd class="font-display text-base font-bold sm:text-lg">{{ $this->formatCount($skills->count()) }}</dd>
                    <dt class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-500 sm:text-[11px]">Skills</dt>
                </div>
                <span class="h-8 w-px shrink-0 bg-slate-100 dark:bg-slate-800"></span>
                <div class="min-w-0 flex-1 text-center">
                    <dd class="font-display text-base font-bold text-brand-600 sm:text-lg">
                        {{ $this->formatCount((int) ($user?->stats?->total_xp ?? 0)) }}
                    </dd>
                    <dt class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-500 sm:text-[11px]">XP</dt>
                </div>
            </dl>
        </div>
    </section>

    {{-- ============================================================
         ABAS — scroll horizontal em mobile, sem quebra
         ============================================================ --}}
    <div class="rounded-2xl bg-white p-1.5 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
        <div class="flex gap-1 overflow-x-auto scrollbar-none">
            @php
                $tabs = [
                    'about'      => ['Sobre',           null,  null],
                    'skills'     => ['Skills',          $skills->count(),      null],
                    'experience' => ['Experiências',    $experiences->count(), null],
                    'education'  => ['Formação',        $educations->count(),  null],
                    'portfolio'  => ['Portfólio',       $portfolioItems->count(), null],
                    'posts'      => ['Posts',           $posts->count(),       null],
                    'assessments'=> ['Testes',          $attempts->count(),    null],
                    // Nova aba do CV Digital — brand-colored para chamar atenção
                    // Comentada em produção enquanto o feature ainda não está pronto:
                    // 'curriculum' => ['Currículo',       null,  'featured'],
                ];
            @endphp
            @foreach ($tabs as $key => [$label, $count, $variant])
                <button wire:click="setTab('{{ $key }}')"
                        class="flex shrink-0 items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium transition
                               {{ $tab === $key
                                   ? ($variant === 'featured'
                                       ? 'bg-brand-500 text-white shadow-sm'
                                       : 'bg-slate-900 text-white shadow-sm dark:bg-white dark:text-slate-900')
                                   : ($variant === 'featured'
                                       ? 'text-brand-700 hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/10'
                                       : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800') }}">
                    @if ($variant === 'featured')
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2 4 5v7c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V5l-8-3z" opacity=".9"/>
                        </svg>
                    @endif
                    <span>{{ $label }}</span>
                    @if (! is_null($count) && $count > 0)
                        <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none
                                     {{ $tab === $key
                                         ? 'bg-white/20 text-white dark:bg-slate-900/20 dark:text-slate-900'
                                         : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                            {{ $count }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- ============================================================
         CONTEÚDO POR ABA
         ============================================================ --}}
    <section class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 sm:p-6"
             wire:key="tab-{{ $tab }}">
        @switch($tab)
            {{-- SOBRE --}}
            @case('about')
                <h2 class="font-display text-lg font-bold sm:text-xl">Sobre</h2>
                @if ($profile?->bio)
                    <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-700 dark:text-slate-300 sm:text-[15px]">
                        {{ $profile->bio }}
                    </p>
                @else
                    <div class="mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center dark:border-slate-700 dark:bg-slate-800/50">
                        <p class="text-sm text-slate-500">Ainda não há uma biografia disponível.</p>
                    </div>
                @endif

                @if ($profile && ($profile->linkedin_url || $profile->github_url || $profile->portfolio_url))
                    <div class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <p class="mb-2.5 text-xs font-semibold uppercase tracking-wider text-slate-500">Links</p>
                        <div class="flex flex-wrap gap-2">
                            @if ($profile->linkedin_url)
                                <a href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-300">
                                    LinkedIn <x-icon name="arrow-right" class="h-3 w-3"/>
                                </a>
                            @endif
                            @if ($profile->github_url)
                                <a href="{{ $profile->github_url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-800 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">
                                    GitHub <x-icon name="arrow-right" class="h-3 w-3"/>
                                </a>
                            @endif
                            @if ($profile->portfolio_url)
                                <a href="{{ $profile->portfolio_url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300">
                                    Portfólio <x-icon name="arrow-right" class="h-3 w-3"/>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
                @break

            {{-- SKILLS --}}
            @case('skills')
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-lg font-bold sm:text-xl">Skills</h2>
                    @if ($skills->count())
                        <span class="text-xs text-slate-500">{{ $skills->count() }} habilidades</span>
                    @endif
                </div>
                @if ($skills->count())
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700 hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 sm:text-sm">
                                <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                                {{ $skill->name }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center dark:border-slate-700 dark:bg-slate-800/50">
                        <p class="text-sm text-slate-500">Sem skills cadastradas ainda.</p>
                    </div>
                @endif
                @break

            {{-- EXPERIÊNCIAS --}}
            @case('experience')
                <h2 class="font-display text-lg font-bold sm:text-xl">Experiências</h2>
                @if ($experiences->count())
                    <ol class="mt-5 space-y-5">
                        @foreach ($experiences as $exp)
                            <li class="relative pl-9">
                                <span class="absolute left-0 top-1 grid h-7 w-7 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                                    <x-icon name="briefcase" class="h-3.5 w-3.5"/>
                                </span>
                                @if (! $loop->last)
                                    <span class="absolute left-3.5 top-9 bottom-0 -ml-px w-0.5 bg-slate-100 dark:bg-slate-800"></span>
                                @endif

                                <div class="flex flex-wrap items-baseline justify-between gap-x-2 gap-y-0.5">
                                    <p class="font-semibold text-sm text-ink dark:text-white sm:text-base">{{ $exp->role }}</p>
                                    <span class="shrink-0 whitespace-nowrap rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        {{ optional($exp->start_date)->translatedFormat('M/y') }}
                                        —
                                        {{ $exp->current ? 'atual' : optional($exp->end_date)->translatedFormat('M/y') }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-400 sm:text-sm">{{ $exp->company_name }}</p>
                                @if ($exp->description)
                                    <p class="mt-1.5 text-xs leading-relaxed text-slate-600 dark:text-slate-300 sm:text-sm">
                                        {{ $exp->description }}
                                    </p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @else
                    <div class="mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center dark:border-slate-700 dark:bg-slate-800/50">
                        <p class="text-sm text-slate-500">Nenhuma experiência cadastrada.</p>
                    </div>
                @endif
                @break

            {{-- FORMAÇÃO --}}
            @case('education')
                <h2 class="font-display text-lg font-bold sm:text-xl">Formação</h2>
                @if ($educations->count())
                    <ol class="mt-5 space-y-5">
                        @foreach ($educations as $edu)
                            <li class="relative pl-9">
                                <span class="absolute left-0 top-1 grid h-7 w-7 place-items-center rounded-lg bg-accent/10 text-accent">
                                    <x-icon name="academic" class="h-3.5 w-3.5"/>
                                </span>
                                @if (! $loop->last)
                                    <span class="absolute left-3.5 top-9 bottom-0 -ml-px w-0.5 bg-slate-100 dark:bg-slate-800"></span>
                                @endif

                                <div class="flex flex-wrap items-baseline justify-between gap-x-2 gap-y-0.5">
                                    <p class="font-semibold text-sm text-ink dark:text-white sm:text-base">{{ $edu->degree }}</p>
                                    <span class="shrink-0 whitespace-nowrap rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        {{ optional($edu->start_date)->translatedFormat('Y') }}
                                        —
                                        {{ optional($edu->end_date)->translatedFormat('Y') ?: 'atual' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-400 sm:text-sm">{{ $edu->institution }}</p>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <div class="mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center dark:border-slate-700 dark:bg-slate-800/50">
                        <p class="text-sm text-slate-500">Nenhuma formação cadastrada.</p>
                    </div>
                @endif
                @break

            {{-- PORTFÓLIO --}}
            @case('portfolio')
                <h2 class="font-display text-lg font-bold sm:text-xl">Portfólio</h2>
                @if ($portfolioItems->count())
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 sm:gap-4">
                        @foreach ($portfolioItems as $item)
                            <a href="{{ $item->url ?: '#' }}" target="_blank" rel="noopener"
                               class="group overflow-hidden rounded-xl border border-slate-200 bg-white hover:border-brand-500 hover:shadow-soft dark:border-slate-700 dark:bg-slate-800">
                                @if ($item->image_path)
                                    <div class="aspect-video bg-slate-100"
                                         style="background-image:url('{{ \Illuminate\Support\Facades\Storage::url($item->image_path) }}');background-size:cover;background-position:center"></div>
                                @else
                                    <div class="grid aspect-video place-items-center bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400 dark:from-slate-800 dark:to-slate-900">
                                        <x-icon name="briefcase" class="h-8 w-8"/>
                                    </div>
                                @endif
                                <div class="p-3">
                                    <p class="text-sm font-semibold text-ink group-hover:text-brand-600 dark:text-white">{{ $item->title }}</p>
                                    @if ($item->description)
                                        <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $item->description }}</p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="mt-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center dark:border-slate-700 dark:bg-slate-800/50">
                        <p class="text-sm text-slate-500">Portfólio vazio.</p>
                    </div>
                @endif
                @break

            {{-- POSTS --}}
            @case('posts')
                <div class="mb-4 flex items-center gap-2">
                    <div class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-400">
                        <x-icon name="chat" class="h-4 w-4"/>
                    </div>
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white sm:text-xl">
                        Publicações recentes
                    </h2>
                </div>

                @if ($posts->count())
                    <div class="space-y-4">
                        @foreach ($posts as $post)
                            <livewire:feed.post-card :post-id="$post->id"
                                                     :wire:key="'profile-post-' . $post->id"/>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center dark:border-slate-700 dark:bg-slate-800/50">
                        <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                            <x-icon name="chat" class="h-6 w-6"/>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-300">
                            Nenhuma publicação ainda
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            @if ($isOwner)
                                Compartilhe algo no feed pra ver aqui.
                            @else
                                Este usuário ainda não publicou nada.
                            @endif
                        </p>
                        @if ($isOwner)
                            <a href="{{ route('feed') }}" wire:navigate class="btn-primary mt-4 inline-flex text-xs">
                                Ir para o feed
                            </a>
                        @endif
                    </div>
                @endif
                @break

            {{-- ================ TESTES REALIZADOS ================ --}}
            @case('assessments')
                <div class="mb-5 flex items-center gap-2">
                    <div class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-400">
                        {{-- Ícone: clipboard com check --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016Z"/>
                        </svg>
                    </div>
                    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-white sm:text-xl">
                        Testes realizados
                    </h2>
                </div>

                @if ($attempts->count() > 0)
                    {{-- Resumo agregado --}}
                    @php
                        $passedCount = $attempts->where('passed', true)->count();
                        $averageScore = round($attempts->avg('score') ?? 0);
                    @endphp
                    <div class="mb-5 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-slate-50 p-3 text-center dark:bg-slate-800/50">
                            <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $attempts->count() }}</p>
                            <p class="text-[11px] uppercase tracking-wide text-slate-500">Realizados</p>
                        </div>
                        <div class="rounded-2xl bg-emerald-50 p-3 text-center dark:bg-emerald-500/10">
                            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $passedCount }}</p>
                            <p class="text-[11px] uppercase tracking-wide text-emerald-700/70 dark:text-emerald-300/70">Aprovados</p>
                        </div>
                        <div class="rounded-2xl bg-brand-50 p-3 text-center dark:bg-brand-500/10">
                            <p class="text-2xl font-bold text-brand-700 dark:text-brand-300">{{ $averageScore }}%</p>
                            <p class="text-[11px] uppercase tracking-wide text-brand-700/70 dark:text-brand-300/70">Média</p>
                        </div>
                    </div>

                    {{-- Lista de testes --}}
                    <ul class="space-y-3">
                        @foreach ($attempts as $attempt)
                            @php
                                $a = $attempt->assessment;
                                if (! $a) continue;
                                $score = (int) $attempt->score;
                                $passed = (bool) $attempt->passed;
                                $bar = $passed ? 'bg-emerald-500' : 'bg-rose-500';
                                $badge = $passed
                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300'
                                    : 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300';
                            @endphp
                            <li class="rounded-2xl border border-slate-100 bg-white p-4 transition hover:border-brand-200 hover:shadow-soft dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-brand-500/40">
                                <div class="flex items-start gap-3">
                                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-semibold text-slate-900 dark:text-white">
                                                {{ $a->title ?? 'Teste' }}
                                            </h3>
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badge }}">
                                                @if ($passed)
                                                    Aprovado
                                                @else
                                                    Não aprovado
                                                @endif
                                            </span>
                                        </div>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            @if ($a->category)
                                                <span class="capitalize">{{ $a->category }}</span> ·
                                            @endif
                                            @if ($a->difficulty)
                                                <span class="capitalize">{{ $a->difficulty }}</span> ·
                                            @endif
                                            {{ $attempt->finished_at?->diffForHumans() }}
                                        </p>

                                        {{-- Barra de progresso da pontuação --}}
                                        <div class="mt-3 flex items-center gap-3">
                                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                                                <div class="h-full {{ $bar }} transition-all"
                                                     style="width: {{ min(100, max(0, $score)) }}%"></div>
                                            </div>
                                            <span class="shrink-0 text-sm font-bold text-slate-900 dark:text-white tabular-nums">
                                                {{ $score }}%
                                            </span>
                                        </div>
                                        @if ($a->passing_score)
                                            <p class="mt-1 text-[10px] text-slate-400">
                                                Nota de corte: {{ $a->passing_score }}%
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center dark:border-slate-700 dark:bg-slate-800/50">
                        <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/>
                            </svg>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-300">
                            Nenhum teste realizado ainda
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            @if ($isOwner)
                                Faça testes para comprovar suas habilidades — aparecem aqui pra empresas verem.
                            @else
                                Este usuário ainda não fez testes de habilidade.
                            @endif
                        </p>
                        @if ($isOwner)
                            <a href="{{ route('skill-assessments.index') }}" wire:navigate class="btn-primary mt-4 inline-flex text-xs">
                                Explorar testes
                            </a>
                        @endif
                    </div>
                @endif
                @break

            {{-- ================ CURRÍCULO DIGITAL ================ --}}
            @case('curriculum')
                @if (! empty($cvData))
                    <div class="-m-4 sm:-m-5">
                        {{-- Barra de ações: só aparece pra visitantes/o dono --}}
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-gradient-to-r from-brand-50 to-accent/5 px-4 py-3 dark:border-slate-800 dark:from-brand-500/10 dark:to-accent/5">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-brand-600" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2 4 5v7c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V5l-8-3z"/>
                                </svg>
                                <p class="text-xs font-semibold text-brand-700 dark:text-brand-300">
                                    Currículo Digital · Verificado
                                </p>
                            </div>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('cv.public', ['username' => $user->username ?? $user->id]) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 rounded-full bg-brand-500 px-3 py-1 text-xs font-semibold text-white hover:bg-brand-600">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                        <polyline points="15 3 21 3 21 9"/>
                                        <line x1="10" y1="14" x2="21" y2="3"/>
                                    </svg>
                                    Ver página pública
                                </a>
                                <button type="button"
                                        x-data="{ copied: false }"
                                        @click="navigator.clipboard.writeText('{{ url(route('cv.public', ['username' => $user->username ?? $user->id])) }}'); copied = true; setTimeout(() => copied = false, 1500);"
                                        class="inline-flex items-center gap-1 rounded-full border border-brand-200 bg-white px-3 py-1 text-xs font-semibold text-brand-700 hover:bg-brand-50 dark:border-brand-500/30 dark:bg-slate-900 dark:text-brand-300">
                                    <span x-text="copied ? 'Copiado!' : 'Copiar link'"></span>
                                </button>
                            </div>
                        </div>

                        @include('livewire.profile.partials.curriculum-body', $cvData)
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-center dark:border-slate-700 dark:bg-slate-800/50">
                        <p class="text-sm text-slate-500">Currículo indisponível.</p>
                    </div>
                @endif
                @break
        @endswitch
    </section>
</div>
