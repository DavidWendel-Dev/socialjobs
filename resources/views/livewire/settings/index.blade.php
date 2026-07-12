<div class="grid grid-cols-12 gap-4">
    {{-- ============================================================
         Sidebar de abas
         ============================================================ --}}
    <aside class="col-span-12 lg:col-span-3">
        <div class="card sticky top-24 !p-2">
            <div class="mb-2 px-3 py-2">
                <h1 class="font-display text-base font-bold">Configurações</h1>
                <p class="text-xs text-slate-500">Gerencie sua conta</p>
            </div>

            <ul class="space-y-1 text-sm">
                @php
                    $tabs = [
                        'account'       => ['label' => 'Conta',        'icon' => 'user'],
                        'security'      => ['label' => 'Segurança',    'icon' => 'cog'],
                        'privacy'       => ['label' => 'Privacidade',  'icon' => 'check'],
                        'notifications' => ['label' => 'Notificações', 'icon' => 'bell'],
                        'lgpd'          => ['label' => 'Seus dados',   'icon' => 'trash'],
                    ];
                @endphp

                @foreach ($tabs as $key => $item)
                    <li>
                        <button type="button"
                                wire:click="setTab('{{ $key }}')"
                                class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left transition
                                       {{ $tab === $key
                                           ? 'bg-brand-50 font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300'
                                           : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800' }}">
                            <x-icon :name="$item['icon']" class="h-4 w-4"/>
                            {{ $item['label'] }}
                        </button>
                    </li>
                @endforeach

                <hr class="my-2 border-slate-100 dark:border-slate-800">

                {{-- Atalho para Editar perfil (é outra página) --}}
                <li>
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-2 rounded-xl px-3 py-2 text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">
                        <x-icon name="pencil" class="h-4 w-4"/>
                        Editar perfil
                        <x-icon name="arrow-right" class="ml-auto h-3.5 w-3.5"/>
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    {{-- ============================================================
         Conteúdo da aba
         ============================================================ --}}
    <div class="col-span-12 space-y-4 lg:col-span-9">

        @if (session('status'))
            <div class="rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300">
                {{ session('status') }}
            </div>
        @endif

        @switch($tab)

            {{-- ================ Aba: CONTA ================ --}}
            @case('account')
                <div class="card space-y-5">
                    <div>
                        <h2 class="font-display text-lg font-bold">Conta</h2>
                        <p class="text-xs text-slate-500">E-mail e nome de exibição para login e comunicações.</p>
                    </div>

                    <form wire:submit.prevent="saveAccount" class="space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Nome</label>
                            <input type="text" wire:model="name" class="input">
                            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium">E-mail</label>
                            <input type="email" wire:model="email" class="input">
                            <p class="mt-1 text-xs text-slate-500">Usado para login e notificações.</p>
                            @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="btn-primary">Salvar</button>
                        </div>
                    </form>

                    <div class="rounded-xl bg-slate-50 p-4 text-xs text-slate-600 dark:bg-slate-800/50 dark:text-slate-300">
                        💡 Para mudar sua foto, capa, headline, bio e skills, use
                        <a href="{{ route('profile.edit') }}" class="font-semibold text-brand-600 hover:underline">Editar perfil</a>.
                    </div>
                </div>
                @break

            {{-- ================ Aba: SEGURANÇA ================ --}}
            @case('security')
                <div class="card space-y-5">
                    <div>
                        <h2 class="font-display text-lg font-bold">Segurança</h2>
                        <p class="text-xs text-slate-500">Autenticação em 2 fatores e sessões ativas.</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">Autenticação de 2 fatores</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Adicione uma camada extra de proteção usando um app como Google Authenticator ou 1Password.
                                </p>
                            </div>
                            <button type="button" class="btn-secondary" disabled title="Em breve">
                                Ativar
                            </button>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                        <p class="font-semibold">Alterar senha</p>
                        <p class="mt-1 text-xs text-slate-500">
                            Envie um e-mail com um link para redefinir sua senha.
                        </p>
                        <a href="{{ route('password.request') }}" class="btn-secondary mt-3 inline-flex">
                            Enviar link
                        </a>
                    </div>
                </div>
                @break

            {{-- ================ Aba: PRIVACIDADE ================ --}}
            @case('privacy')
                <div class="card space-y-5">
                    <div>
                        <h2 class="font-display text-lg font-bold">Privacidade</h2>
                        <p class="text-xs text-slate-500">Controle como você aparece na plataforma.</p>
                    </div>

                    <form wire:submit.prevent="savePrivacy" class="space-y-3">
                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 cursor-pointer dark:border-slate-700">
                            <input type="checkbox"
                                   wire:model="open_to_work"
                                   class="mt-0.5 h-5 w-5 rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                            <span class="flex-1">
                                <span class="block text-sm font-medium">Aberto a oportunidades</span>
                                <span class="block text-xs text-slate-500">
                                    Um selo verde no seu perfil sinaliza que você está buscando vagas.
                                </span>
                            </span>
                        </label>

                        <button type="submit" class="btn-primary">Salvar preferências</button>
                    </form>
                </div>
                @break

            {{-- ================ Aba: NOTIFICAÇÕES ================ --}}
            @case('notifications')
                <div class="card space-y-5">
                    <div>
                        <h2 class="font-display text-lg font-bold">Notificações</h2>
                        <p class="text-xs text-slate-500">Escolha o que você quer receber.</p>
                    </div>

                    <div class="space-y-2">
                        @foreach ([
                            'Novas vagas compatíveis com seu perfil',
                            'Novas mensagens diretas',
                            'Alguém reagiu ou comentou no seu post',
                            'Alguém começou a te seguir',
                            'Atualizações de candidaturas',
                            'Novos cursos disponíveis',
                        ] as $option)
                            <label class="flex items-center justify-between rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                                <span class="text-sm">{{ $option }}</span>
                                <input type="checkbox" checked
                                       class="h-5 w-5 rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                            </label>
                        @endforeach
                    </div>

                    <p class="text-xs text-slate-500">
                        As preferências de notificação serão salvas em uma próxima atualização.
                    </p>
                </div>
                @break

            {{-- ================ Aba: LGPD ================ --}}
            @case('lgpd')
                <div class="card space-y-5">
                    <div>
                        <h2 class="font-display text-lg font-bold">Seus dados (LGPD)</h2>
                        <p class="text-xs text-slate-500">
                            Direitos garantidos pela Lei Geral de Proteção de Dados.
                        </p>
                    </div>

                    {{-- Exportar --}}
                    <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-500/10 text-brand-600">
                                <x-icon name="arrow-up" class="h-5 w-5"/>
                            </span>
                            <div class="flex-1">
                                <p class="font-semibold">Exportar meus dados</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Você receberá um arquivo com todos os dados que temos sobre você (perfil, posts, candidaturas, cursos etc.). Enviado por e-mail em até 24h.
                                </p>
                            </div>
                            <button type="button"
                                    wire:click="exportData"
                                    wire:loading.attr="disabled"
                                    class="btn-secondary shrink-0">
                                Solicitar
                            </button>
                        </div>
                    </div>

                    {{-- Excluir --}}
                    <div class="rounded-xl border border-rose-200 bg-rose-50/40 p-4 dark:border-rose-500/30 dark:bg-rose-500/5">
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-500/10 text-rose-600">
                                <x-icon name="trash" class="h-5 w-5"/>
                            </span>
                            <div class="flex-1">
                                <p class="font-semibold text-rose-800 dark:text-rose-300">Excluir minha conta</p>
                                <p class="mt-1 text-xs text-rose-700/80 dark:text-rose-300/70">
                                    Sua conta e todos os dados associados serão excluídos em 15 dias.
                                    Você pode cancelar essa solicitação a qualquer momento antes desse prazo.
                                </p>

                                <div class="mt-3">
                                    <label class="mb-1 block text-xs font-medium text-rose-800 dark:text-rose-300">
                                        Por que você quer excluir? (opcional)
                                    </label>
                                    <textarea wire:model="deletionReason"
                                              rows="3"
                                              class="input !border-rose-200 dark:!border-rose-500/30"
                                              placeholder="Sua opinião ajuda a melhorarmos o produto."></textarea>
                                </div>

                                <button type="button"
                                        wire:click="requestDeletion"
                                        wire:confirm="Tem certeza que quer solicitar a exclusão da sua conta? Ela será excluída em 15 dias."
                                        class="mt-3 inline-flex items-center gap-2 rounded-2xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                                    <x-icon name="trash" class="h-4 w-4"/> Solicitar exclusão
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @break
        @endswitch
    </div>
</div>
