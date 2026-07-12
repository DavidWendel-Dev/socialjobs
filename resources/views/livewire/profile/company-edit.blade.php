<div class="space-y-6">
    {{-- ============================================================
         HERO — Capa + Logo (mesmo padrão do perfil de candidato)
         ============================================================ --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800">
        {{-- Capa --}}
        <div class="group relative h-40 sm:h-56 w-full bg-gradient-to-br from-brand-500 to-accent-500">
            @if ($user->cover_path)
                <img src="{{ $user->cover_url }}"
                     alt="Capa da empresa"
                     class="h-full w-full object-cover">
            @endif

            {{-- Overlay com botões --}}
            <div class="absolute inset-0 flex items-end justify-end p-3 opacity-0 transition group-hover:opacity-100 focus-within:opacity-100">
                <div class="flex gap-2 rounded-xl bg-black/50 p-1.5 backdrop-blur-sm">
                    <label class="cursor-pointer inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 hover:bg-slate-100">
                        <x-icon name="sparkles" class="h-3.5 w-3.5"/>
                        <span wire:loading.remove wire:target="coverUpload">Trocar capa</span>
                        <span wire:loading wire:target="coverUpload">Enviando...</span>
                        <input type="file" wire:model="coverUpload" accept="image/*" class="hidden">
                    </label>
                    @if ($user->cover_path)
                        <button type="button"
                                wire:click="removeCover"
                                wire:confirm="Remover a capa da empresa?"
                                class="inline-flex items-center gap-1 rounded-lg bg-rose-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-600">
                            <x-icon name="x" class="h-3.5 w-3.5"/>
                        </button>
                    @endif
                </div>
            </div>
            @error('coverUpload')
                <p class="absolute bottom-2 left-3 rounded-lg bg-rose-500 px-2 py-1 text-xs text-white">{{ $message }}</p>
            @enderror
        </div>

        {{-- Logo + Nome --}}
        <div class="relative px-4 sm:px-6 pb-4 sm:pb-6">
            <div class="-mt-12 sm:-mt-16 flex items-end gap-4">
                {{-- Logo --}}
                <div class="group relative flex-shrink-0">
                    <div class="h-24 w-24 sm:h-28 sm:w-28 overflow-hidden rounded-2xl border-4 border-white bg-white shadow-soft-lg dark:border-slate-900">
                        @if ($user->avatar_path)
                            <img src="{{ $user->avatar_url }}"
                                 alt="Logo da empresa"
                                 class="h-full w-full object-cover">
                        @else
                            <div class="grid h-full w-full place-items-center bg-gradient-to-br from-brand-100 to-brand-200 text-3xl sm:text-4xl font-display font-bold text-brand-700">
                                {{ mb_substr($user->name ?? 'E', 0, 1) }}
                            </div>
                        @endif
                    </div>
                    {{-- Botão trocar logo --}}
                    <label class="absolute -bottom-1 -right-1 cursor-pointer grid h-8 w-8 place-items-center rounded-full bg-brand-500 text-white shadow-soft hover:bg-brand-600 transition">
                        <x-icon name="sparkles" class="h-4 w-4"/>
                        <input type="file" wire:model="avatarUpload" accept="image/*" class="hidden">
                    </label>
                    <div wire:loading wire:target="avatarUpload"
                         class="absolute inset-0 grid place-items-center rounded-2xl bg-white/70 text-xs font-semibold text-brand-700">
                        Enviando...
                    </div>
                </div>

                {{-- Nome / dica --}}
                <div class="min-w-0 flex-1 pb-1 sm:pb-2">
                    <h1 class="truncate font-display text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">
                        {{ $profile->trade_name ?: ($profile->legal_name ?: $user->name) }}
                    </h1>
                    <p class="truncate text-xs sm:text-sm text-slate-500">
                        {{ $profile->trade_name ?: $profile->legal_name }}
                    </p>
                </div>

                {{-- Ver perfil público --}}
                @if ($profile->slug)
                    <a href="{{ url('/c/' . $profile->slug) }}" target="_blank"
                       class="hidden sm:inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                        <x-icon name="arrow-right" class="h-3.5 w-3.5"/>
                        Ver perfil público
                    </a>
                @endif
            </div>

            @error('avatarUpload')
                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- ============================================================
         Formulário — dados da empresa
         ============================================================ --}}
    <form wire:submit="save" class="card space-y-5">
        <div>
            <h2 class="font-display text-lg font-bold">Dados da empresa</h2>
            <p class="text-xs sm:text-sm text-slate-500">Essas informações aparecem no seu perfil público (/c/{{ $profile->slug ?? '...' }}).</p>
        </div>

        {{-- Nome de exibição --}}
        <div>
            <label for="name" class="mb-1 block text-sm font-medium">Nome de exibição *</label>
            <p class="mb-2 text-xs text-slate-500">Como sua empresa aparece no site (feed, comentários, header).</p>
            <input type="text" id="name" wire:model="name" class="input" maxlength="255" required>
            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            {{-- Razão social --}}
            <div>
                <label for="legal_name" class="mb-1 block text-sm font-medium">Razão social *</label>
                <input type="text" id="legal_name" wire:model="legal_name" class="input" maxlength="255" required>
                @error('legal_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Nome fantasia --}}
            <div>
                <label for="trade_name" class="mb-1 block text-sm font-medium">Nome fantasia</label>
                <input type="text" id="trade_name" wire:model="trade_name" class="input" maxlength="255">
                @error('trade_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- CNPJ (somente leitura, informativo) --}}
            <div>
                <label class="mb-1 block text-sm font-medium">CNPJ</label>
                <input type="text" value="{{ $profile->cnpj ? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $profile->cnpj) : '—' }}"
                       class="input bg-slate-50 dark:bg-slate-800" readonly>
                <p class="mt-1 text-xs text-slate-500">O CNPJ não pode ser alterado após o cadastro.</p>
            </div>

            {{-- Setor / Indústria --}}
            <div>
                <label for="industry" class="mb-1 block text-sm font-medium">Setor</label>
                <input type="text" id="industry" wire:model="industry" class="input" maxlength="255" placeholder="Ex: Tecnologia, Varejo, Saúde">
                @error('industry') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Porte --}}
            <div>
                <label for="size" class="mb-1 block text-sm font-medium">Porte</label>
                <select id="size" wire:model="size" class="input">
                    <option value="">Não informado</option>
                    <option value="1-10">1 a 10 funcionários</option>
                    <option value="11-50">11 a 50 funcionários</option>
                    <option value="51-200">51 a 200 funcionários</option>
                    <option value="201-500">201 a 500 funcionários</option>
                    <option value="501+">Mais de 500 funcionários</option>
                </select>
                @error('size') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Telefone --}}
            <div>
                <label for="phone" class="mb-1 block text-sm font-medium">Telefone</label>
                <input type="text" id="phone" wire:model="phone" class="input" maxlength="30" placeholder="(11) 99999-9999">
                @error('phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Website --}}
            <div class="sm:col-span-2">
                <label for="website" class="mb-1 block text-sm font-medium">Website</label>
                <input type="url" id="website" wire:model="website" class="input" maxlength="191" placeholder="https://empresa.com.br">
                @error('website') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Sobre --}}
        <div>
            <label for="about" class="mb-1 block text-sm font-medium">Sobre a empresa</label>
            <p class="mb-2 text-xs text-slate-500">Conte a história, missão e valores. Aparece na aba "Sobre" do perfil público.</p>
            <textarea id="about" wire:model="about" rows="6" maxlength="2000"
                      class="input resize-y"
                      placeholder="Somos uma empresa que..."></textarea>
            <div class="mt-1 flex justify-between text-xs text-slate-500">
                @error('about')
                    <span class="text-rose-600">{{ $message }}</span>
                @else
                    <span>Máximo 2000 caracteres.</span>
                @enderror
                <span>{{ mb_strlen($about) }}/2000</span>
            </div>
        </div>

        {{-- Ações --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
            <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Salvar alterações</span>
                <span wire:loading wire:target="save">Salvando...</span>
            </button>
        </div>

        @if (session('status'))
            <div class="rounded-xl bg-brand-50 p-3 text-sm text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                {{ session('status') }}
            </div>
        @endif
    </form>
</div>
