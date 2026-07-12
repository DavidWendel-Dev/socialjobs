<div class="space-y-4">
    {{-- ============================================================
         Header da página
         ============================================================ --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold">Editar perfil</h1>
            <p class="text-sm text-slate-500">Atualize suas informações profissionais</p>
        </div>
        <a href="{{ url('/u/' . (auth()->user()->username ?? auth()->user()->id)) }}"
           class="btn-ghost text-sm">
            <x-icon name="arrow-right" class="mr-1 h-4 w-4"/>
            Ver meu perfil
        </a>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-4">

        {{-- ============================================================
             Card 1 — Foto de capa e avatar
             ============================================================ --}}
        <section class="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
            {{-- Capa --}}
            <div class="group relative">
                @if (auth()->user()->cover_path)
                    <div class="h-32 sm:h-44"
                         style="background-image:url('{{ auth()->user()->cover_url }}');background-size:cover;background-position:center"></div>
                @else
                    <div class="relative h-32 bg-gradient-to-br from-brand-500 to-accent sm:h-44">
                        <div class="absolute inset-0 opacity-25"
                             style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,.5) 0, transparent 40%), radial-gradient(circle at 80% 60%, rgba(255,255,255,.3) 0, transparent 40%);"></div>
                    </div>
                @endif

                {{-- Overlay para trocar capa --}}
                <label for="editCoverUpload"
                       class="absolute inset-0 flex cursor-pointer items-center justify-center bg-black/40 opacity-100 transition sm:opacity-0 sm:group-hover:opacity-100"
                       wire:loading.class="!opacity-100"
                       wire:target="coverUpload,updatedCoverUpload">
                    <div wire:loading.remove wire:target="coverUpload,updatedCoverUpload"
                         class="flex flex-col items-center gap-1 text-white">
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-white/20 backdrop-blur">
                            <x-icon name="camera" class="h-5 w-5"/>
                        </span>
                        <span class="text-xs font-medium">
                            {{ auth()->user()->cover_path ? 'Trocar capa' : 'Adicionar capa' }}
                        </span>
                    </div>
                    <div wire:loading wire:target="coverUpload,updatedCoverUpload"
                         class="text-white text-xs">Enviando...</div>
                    <input type="file" id="editCoverUpload" wire:model="coverUpload"
                           accept="image/jpeg,image/png,image/webp" class="sr-only">
                </label>

                @if (auth()->user()->cover_path)
                    <button type="button"
                            wire:click="removeCover"
                            wire:confirm="Remover capa atual?"
                            class="absolute right-2 top-2 z-10 grid h-8 w-8 place-items-center rounded-full bg-black/60 text-white opacity-100 transition hover:bg-rose-600 sm:opacity-0 sm:group-hover:opacity-100"
                            title="Remover capa">
                        <x-icon name="trash" class="h-4 w-4"/>
                    </button>
                @endif
            </div>
            @error('coverUpload')<p class="bg-rose-50 px-4 py-1 text-xs text-rose-600">{{ $message }}</p>@enderror

            {{-- Avatar --}}
            <div class="flex flex-col gap-3 px-6 pb-6 sm:flex-row sm:items-end">
                <div class="relative -mt-14 shrink-0 sm:-mt-16">
                    <div class="group/avatar relative">
                        <x-avatar :user="auth()->user()" size="lg"
                                  class="!h-24 !w-24 !text-2xl ring-4 ring-white shadow-soft dark:ring-slate-900 sm:!h-28 sm:!w-28 sm:!text-3xl"/>

                        <label for="editAvatarUpload"
                               class="absolute inset-0 hidden cursor-pointer items-center justify-center rounded-full bg-black/50 text-white opacity-0 transition group-hover/avatar:opacity-100 sm:flex"
                               wire:loading.class="!opacity-100"
                               wire:target="avatarUpload,updatedAvatarUpload">
                            <x-icon name="camera" class="h-6 w-6"/>
                            <input type="file" id="editAvatarUpload" wire:model="avatarUpload"
                                   accept="image/jpeg,image/png,image/webp" class="sr-only">
                        </label>

                        {{-- Botão flutuante mobile --}}
                        <label for="editAvatarUpload"
                               class="absolute -bottom-0.5 -right-0.5 grid h-8 w-8 cursor-pointer place-items-center rounded-full bg-brand-500 text-white shadow-soft ring-4 ring-white transition hover:bg-brand-600 dark:ring-slate-900 sm:hidden"
                               title="Trocar foto">
                            <x-icon name="camera" class="h-4 w-4"/>
                            <input type="file" wire:model="avatarUpload"
                                   accept="image/jpeg,image/png,image/webp" class="sr-only">
                        </label>
                    </div>
                </div>

                <div class="flex-1 pb-1">
                    <p class="text-sm font-medium">Foto de perfil</p>
                    <p class="text-xs text-slate-500">JPG, PNG ou WEBP · até 2 MB</p>
                    @error('avatarUpload')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                @if (auth()->user()->avatar_path)
                    <button type="button"
                            wire:click="removeAvatar"
                            wire:confirm="Remover sua foto de perfil?"
                            class="btn-ghost !text-xs !text-rose-600">
                        <x-icon name="trash" class="mr-1 h-3.5 w-3.5"/> Remover foto
                    </button>
                @endif
            </div>
        </section>

        {{-- ============================================================
             Card 2 — Informações básicas
             ============================================================ --}}
        <section class="card space-y-4">
            <div>
                <h2 class="font-display text-lg font-bold">Informações básicas</h2>
                <p class="text-xs text-slate-500">Como você aparece para outras pessoas</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Nome completo</label>
                    <input type="text" wire:model="name" class="input" placeholder="Ex: João Silva">
                    @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Username</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">&#64;</span>
                        <input type="text" wire:model="username" class="input pl-8" placeholder="joao-silva">
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Aparece em SocialJobs.com.br/u/&lt;username&gt;</p>
                    @error('username') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium">Headline profissional</label>
                    <input type="text" wire:model="headline" class="input"
                           placeholder="Ex: Desenvolvedor Full Stack apaixonado por Laravel e React">
                    <p class="mt-1 text-xs text-slate-500">Uma frase curta que resume o que você faz</p>
                    @error('headline') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Localização</label>
                    <input type="text" wire:model="location" class="input" placeholder="Ex: São Paulo, SP">
                </div>

                <div class="flex items-center">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox"
                               wire:model="open_to_work"
                               class="h-5 w-5 rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                        <span class="text-sm">
                            <strong>Aberto a oportunidades</strong>
                            <p class="text-xs text-slate-500">Um selo verde aparece no seu perfil</p>
                        </span>
                    </label>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Bio</label>
                <textarea wire:model="bio" rows="5" class="input"
                          placeholder="Conte sua trajetória, o que faz, no que se especializa..."></textarea>
                <div class="mt-1 flex items-center justify-between text-xs text-slate-500">
                    <span>Fale um pouco sobre você em até 2000 caracteres</span>
                    <span>{{ mb_strlen($bio) }}/2000</span>
                </div>
                @error('bio') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </section>

        {{-- ============================================================
             Card 3 — Skills
             ============================================================ --}}
        <section class="card space-y-4">
            <div>
                <h2 class="font-display text-lg font-bold">Skills</h2>
                <p class="text-xs text-slate-500">Adicione até 20 habilidades. Elas ajudam no match com vagas.</p>
            </div>

            <div class="flex gap-2">
                <input type="text" wire:model="skillInput"
                       wire:keydown.enter.prevent="addSkill"
                       class="input flex-1"
                       placeholder="Ex: Laravel, React, Figma..."
                       maxlength="50">
                <button type="button"
                        wire:click="addSkill"
                        class="btn-primary shrink-0"
                        @disabled(count($skills) >= 20)>
                    <x-icon name="plus" class="mr-1 h-4 w-4"/> Adicionar
                </button>
            </div>
            @error('skillInput') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror

            @if (count($skills))
                <div class="flex flex-wrap gap-2">
                    @foreach ($skills as $index => $skill)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 pl-3 pr-1 py-1 text-sm font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                            {{ $skill }}
                            <button type="button"
                                    wire:click="removeSkill({{ $index }})"
                                    class="ml-1 grid h-5 w-5 place-items-center rounded-full text-slate-400 hover:bg-rose-100 hover:text-rose-600"
                                    title="Remover">
                                <x-icon name="x" class="h-3 w-3"/>
                            </button>
                        </span>
                    @endforeach
                </div>
                <p class="text-xs text-slate-400">{{ count($skills) }}/20 skills</p>
            @else
                <p class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-xs text-slate-500 dark:border-slate-700">
                    Nenhuma skill ainda. Comece adicionando as tecnologias e ferramentas que você domina.
                </p>
            @endif
        </section>

        {{-- ============================================================
             Card 4 — Links externos
             ============================================================ --}}
        <section class="card space-y-4">
            <div>
                <h2 class="font-display text-lg font-bold">Links</h2>
                <p class="text-xs text-slate-500">Opcional. Aparecem na aba "Sobre" do seu perfil.</p>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="mb-1 flex items-center gap-1.5 text-sm font-medium">
                        <svg class="h-4 w-4 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.5 2h-17A1.5 1.5 0 0 0 2 3.5v17A1.5 1.5 0 0 0 3.5 22h17a1.5 1.5 0 0 0 1.5-1.5v-17A1.5 1.5 0 0 0 20.5 2ZM8 19H5v-9h3v9Zm-1.5-10.28A1.72 1.72 0 1 1 8.22 7 1.72 1.72 0 0 1 6.5 8.72ZM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0 0 13 14.19a.66.66 0 0 0 0 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 0 1 2.7-1.4c1.55 0 3.36.86 3.36 3.66Z"/>
                        </svg>
                        LinkedIn
                    </label>
                    <input type="url" wire:model="linkedin_url" class="input" placeholder="https://linkedin.com/in/seu-perfil">
                    @error('linkedin_url') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 flex items-center gap-1.5 text-sm font-medium">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 .3a12 12 0 0 0-3.8 23.38c.6.12.83-.26.83-.58v-2.24c-3.34.73-4.03-1.43-4.03-1.43-.55-1.4-1.34-1.77-1.34-1.77-1.1-.75.08-.73.08-.73 1.21.09 1.85 1.25 1.85 1.25 1.08 1.86 2.84 1.32 3.53 1.01.1-.78.42-1.32.76-1.62-2.66-.31-5.46-1.34-5.46-5.96 0-1.32.47-2.4 1.24-3.24-.12-.31-.54-1.53.12-3.18 0 0 1-.32 3.3 1.24a11.5 11.5 0 0 1 6 0c2.28-1.56 3.29-1.24 3.29-1.24.66 1.65.24 2.87.12 3.18a4.68 4.68 0 0 1 1.24 3.24c0 4.63-2.8 5.65-5.48 5.95.43.37.81 1.1.81 2.22v3.29c0 .32.22.71.83.58A12 12 0 0 0 12 .3Z"/>
                        </svg>
                        GitHub
                    </label>
                    <input type="url" wire:model="github_url" class="input" placeholder="https://github.com/seu-usuario">
                    @error('github_url') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 flex items-center gap-1.5 text-sm font-medium">
                        <x-icon name="briefcase" class="h-4 w-4 text-brand-600"/>
                        Portfólio pessoal
                    </label>
                    <input type="url" wire:model="portfolio_url" class="input" placeholder="https://seusite.com">
                    @error('portfolio_url') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- ============================================================
             Card 5 — Experiências profissionais (CRUD independente)
             Ficam antes do rodapé para o botão "Salvar alterações" ser
             o último elemento da página, com fluxo natural de cima→baixo.
             ============================================================ --}}
        <section class="card !p-5 sm:!p-6" wire:key="exp-section">
            <div class="mb-4 flex items-center justify-between gap-2">
                <div>
                    <h2 class="flex items-center gap-2 font-display text-base font-bold">
                        <x-icon name="briefcase" class="h-4 w-4 text-brand-600"/>
                        Experiências profissionais
                    </h2>
                    <p class="text-xs text-slate-500">
                        Adicione seus empregos anteriores e atual.
                    </p>
                </div>
                @if ($editingExperienceId === null)
                    <button type="button" wire:click="newExperience"
                            class="inline-flex items-center gap-1 rounded-full bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600">
                        <x-icon name="plus" class="h-3.5 w-3.5"/>
                        Adicionar
                    </button>
                @endif
            </div>

            @if ($editingExperienceId !== null)
                <div class="mb-4 rounded-2xl border border-brand-200 bg-brand-50/40 p-4 dark:border-brand-500/30 dark:bg-brand-500/5">
                    <p class="mb-3 text-xs font-bold uppercase tracking-wider text-brand-700 dark:text-brand-300">
                        {{ $editingExperienceId > 0 ? 'Editando experiência' : 'Nova experiência' }}
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-medium">Empresa *</label>
                            <input type="text" wire:model="expCompany" class="input" placeholder="Ex: Acme Tecnologia">
                            @error('expCompany') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-medium">Cargo *</label>
                            <input type="text" wire:model="expRole" class="input" placeholder="Ex: Desenvolvedor Full-Stack">
                            @error('expRole') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium">Início *</label>
                            <input type="date" wire:model="expStartDate" class="input">
                            @error('expStartDate') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium">Fim</label>
                            <input type="date" wire:model="expEndDate" class="input"
                                   @disabled($expCurrent)>
                            @error('expEndDate') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <label class="sm:col-span-2 flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" wire:model.live="expCurrent"
                                   class="h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                            <span>Ainda trabalho aqui</span>
                        </label>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-medium">Descrição das atividades</label>
                            <textarea wire:model="expDescription" rows="3" class="input"
                                      placeholder="O que você fez? Principais conquistas..."
                                      maxlength="2000"></textarea>
                            @error('expDescription') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-end gap-2">
                        <button type="button" wire:click="cancelExperience" class="btn-ghost text-xs">Cancelar</button>
                        <button type="button" wire:click="saveExperience"
                                wire:loading.attr="disabled"
                                wire:target="saveExperience"
                                class="btn-primary !py-2 text-xs">
                            <span wire:loading.remove wire:target="saveExperience">Salvar experiência</span>
                            <span wire:loading wire:target="saveExperience">Salvando...</span>
                        </button>
                    </div>
                </div>
            @endif

            @if ($experiences->count())
                <ul class="space-y-2">
                    @foreach ($experiences as $exp)
                        <li class="flex items-start gap-3 rounded-xl border border-slate-100 p-3 dark:border-slate-800"
                            wire:key="exp-{{ $exp->id }}">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                                <x-icon name="briefcase" class="h-4 w-4"/>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $exp->role }}</p>
                                <p class="truncate text-xs text-slate-600 dark:text-slate-400">{{ $exp->company_name }}</p>
                                <p class="text-[10px] text-slate-500">
                                    {{ optional($exp->start_date)->format('m/Y') }}
                                    —
                                    @if ($exp->current)
                                        <span class="text-brand-600">Atual</span>
                                    @else
                                        {{ optional($exp->end_date)->format('m/Y') ?? '—' }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                <button type="button" wire:click="editExperience({{ $exp->id }})"
                                        class="grid h-7 w-7 place-items-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-brand-600 dark:hover:bg-slate-800">
                                    <x-icon name="pencil" class="h-3.5 w-3.5"/>
                                </button>
                                <button type="button" wire:click="deleteExperience({{ $exp->id }})"
                                        wire:confirm="Remover esta experiência?"
                                        class="grid h-7 w-7 place-items-center rounded-full text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                                    <x-icon name="x" class="h-3.5 w-3.5"/>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @elseif ($editingExperienceId === null)
                <div class="rounded-xl border border-dashed border-slate-200 p-5 text-center dark:border-slate-700">
                    <p class="text-xs text-slate-500">Sem experiências cadastradas.</p>
                </div>
            @endif
        </section>

        {{-- ============================================================
             Card 6 — Formação acadêmica (CRUD independente)
             ============================================================ --}}
        <section class="card !p-5 sm:!p-6" wire:key="edu-section">
            <div class="mb-4 flex items-center justify-between gap-2">
                <div>
                    <h2 class="flex items-center gap-2 font-display text-base font-bold">
                        <x-icon name="academic" class="h-4 w-4 text-accent"/>
                        Formação acadêmica
                    </h2>
                    <p class="text-xs text-slate-500">
                        Cursos, graduações, pós-graduações e certificações.
                    </p>
                </div>
                @if ($editingEducationId === null)
                    <button type="button" wire:click="newEducation"
                            class="inline-flex items-center gap-1 rounded-full bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600">
                        <x-icon name="plus" class="h-3.5 w-3.5"/>
                        Adicionar
                    </button>
                @endif
            </div>

            @if ($editingEducationId !== null)
                <div class="mb-4 rounded-2xl border border-brand-200 bg-brand-50/40 p-4 dark:border-brand-500/30 dark:bg-brand-500/5">
                    <p class="mb-3 text-xs font-bold uppercase tracking-wider text-brand-700 dark:text-brand-300">
                        {{ $editingEducationId > 0 ? 'Editando formação' : 'Nova formação' }}
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-medium">Instituição *</label>
                            <input type="text" wire:model="eduInstitution" class="input"
                                   placeholder="Ex: Universidade Federal do RJ">
                            @error('eduInstitution') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-medium">Curso / Grau *</label>
                            <input type="text" wire:model="eduDegree" class="input"
                                   placeholder="Ex: Bacharelado em Ciência da Computação">
                            @error('eduDegree') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium">Início *</label>
                            <input type="date" wire:model="eduStartDate" class="input">
                            @error('eduStartDate') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium">Fim</label>
                            <input type="date" wire:model="eduEndDate" class="input">
                            @error('eduEndDate') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-end gap-2">
                        <button type="button" wire:click="cancelEducation" class="btn-ghost text-xs">Cancelar</button>
                        <button type="button" wire:click="saveEducation"
                                wire:loading.attr="disabled" wire:target="saveEducation"
                                class="btn-primary !py-2 text-xs">
                            <span wire:loading.remove wire:target="saveEducation">Salvar formação</span>
                            <span wire:loading wire:target="saveEducation">Salvando...</span>
                        </button>
                    </div>
                </div>
            @endif

            @if ($educations->count())
                <ul class="space-y-2">
                    @foreach ($educations as $ed)
                        <li class="flex items-start gap-3 rounded-xl border border-slate-100 p-3 dark:border-slate-800"
                            wire:key="edu-{{ $ed->id }}">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-accent/10 text-accent">
                                <x-icon name="academic" class="h-4 w-4"/>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $ed->degree }}</p>
                                <p class="truncate text-xs text-slate-600 dark:text-slate-400">{{ $ed->institution }}</p>
                                <p class="text-[10px] text-slate-500">
                                    {{ optional($ed->start_date)->format('Y') }}
                                    —
                                    {{ optional($ed->end_date)->format('Y') ?? 'Presente' }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                <button type="button" wire:click="editEducation({{ $ed->id }})"
                                        class="grid h-7 w-7 place-items-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-brand-600 dark:hover:bg-slate-800">
                                    <x-icon name="pencil" class="h-3.5 w-3.5"/>
                                </button>
                                <button type="button" wire:click="deleteEducation({{ $ed->id }})"
                                        wire:confirm="Remover esta formação?"
                                        class="grid h-7 w-7 place-items-center rounded-full text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                                    <x-icon name="x" class="h-3.5 w-3.5"/>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @elseif ($editingEducationId === null)
                <div class="rounded-xl border border-dashed border-slate-200 p-5 text-center dark:border-slate-700">
                    <p class="text-xs text-slate-500">Sem formação cadastrada.</p>
                </div>
            @endif
        </section>

        {{-- ============================================================
             Card 7 — Portfólio (CRUD independente)
             ============================================================ --}}
        <section class="card !p-5 sm:!p-6" wire:key="port-section">
            <div class="mb-4 flex items-center justify-between gap-2">
                <div>
                    <h2 class="flex items-center gap-2 font-display text-base font-bold">
                        <x-icon name="sparkles" class="h-4 w-4 text-brand-600"/>
                        Portfólio
                    </h2>
                    <p class="text-xs text-slate-500">
                        Projetos, trabalhos e cases que quer destacar.
                    </p>
                </div>
                @if ($editingPortfolioId === null)
                    <button type="button" wire:click="newPortfolio"
                            class="inline-flex items-center gap-1 rounded-full bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600">
                        <x-icon name="plus" class="h-3.5 w-3.5"/>
                        Adicionar
                    </button>
                @endif
            </div>

            @if ($editingPortfolioId !== null)
                <div class="mb-4 rounded-2xl border border-brand-200 bg-brand-50/40 p-4 dark:border-brand-500/30 dark:bg-brand-500/5">
                    <p class="mb-3 text-xs font-bold uppercase tracking-wider text-brand-700 dark:text-brand-300">
                        {{ $editingPortfolioId > 0 ? 'Editando item' : 'Novo item do portfólio' }}
                    </p>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium">Título *</label>
                            <input type="text" wire:model="portTitle" class="input"
                                   placeholder="Ex: Sistema de Vendas em Laravel">
                            @error('portTitle') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium">URL</label>
                            <input type="url" wire:model="portUrl" class="input"
                                   placeholder="https://github.com/voce/projeto">
                            @error('portUrl') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium">Descrição</label>
                            <textarea wire:model="portDescription" rows="3" class="input"
                                      placeholder="Sobre o projeto, tecnologias usadas, resultado..."
                                      maxlength="1000"></textarea>
                            @error('portDescription') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-end gap-2">
                        <button type="button" wire:click="cancelPortfolio" class="btn-ghost text-xs">Cancelar</button>
                        <button type="button" wire:click="savePortfolio"
                                wire:loading.attr="disabled" wire:target="savePortfolio"
                                class="btn-primary !py-2 text-xs">
                            <span wire:loading.remove wire:target="savePortfolio">Salvar item</span>
                            <span wire:loading wire:target="savePortfolio">Salvando...</span>
                        </button>
                    </div>
                </div>
            @endif

            @if ($portfolioItems->count())
                <ul class="space-y-2">
                    @foreach ($portfolioItems as $item)
                        <li class="flex items-start gap-3 rounded-xl border border-slate-100 p-3 dark:border-slate-800"
                            wire:key="port-{{ $item->id }}">
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                                <x-icon name="sparkles" class="h-4 w-4"/>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $item->title }}</p>
                                @if ($item->url)
                                    <a href="{{ $item->url }}" target="_blank" rel="noopener"
                                       class="truncate text-xs text-brand-600 hover:underline">
                                        {{ $item->url }}
                                    </a>
                                @endif
                                @if ($item->description)
                                    <p class="line-clamp-2 text-xs text-slate-500">{{ $item->description }}</p>
                                @endif
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                <button type="button" wire:click="editPortfolio({{ $item->id }})"
                                        class="grid h-7 w-7 place-items-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-brand-600 dark:hover:bg-slate-800">
                                    <x-icon name="pencil" class="h-3.5 w-3.5"/>
                                </button>
                                <button type="button" wire:click="deletePortfolio({{ $item->id }})"
                                        wire:confirm="Remover este item?"
                                        class="grid h-7 w-7 place-items-center rounded-full text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                                    <x-icon name="x" class="h-3.5 w-3.5"/>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @elseif ($editingPortfolioId === null)
                <div class="rounded-xl border border-dashed border-slate-200 p-5 text-center dark:border-slate-700">
                    <p class="text-xs text-slate-500">Sem itens no portfólio.</p>
                </div>
            @endif
        </section>

        {{-- ============================================================
             Rodapé — botões de ação (sticky no mobile)
             ============================================================ --}}
        <div class="sticky bottom-0 -mx-4 flex items-center justify-end gap-2 border-t border-slate-100 bg-white/95 px-4 py-3 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 sm:static sm:mx-0 sm:border-0 sm:bg-transparent sm:p-0 sm:pt-2">
            <a href="{{ url('/u/' . (auth()->user()->username ?? auth()->user()->id)) }}"
               class="btn-ghost">Cancelar</a>
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="btn-primary">
                <span wire:loading.remove wire:target="save">Salvar alterações</span>
                <span wire:loading wire:target="save">Salvando...</span>
            </button>
        </div>
    </form>
</div>
