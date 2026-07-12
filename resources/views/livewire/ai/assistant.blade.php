<div class="mx-auto max-w-5xl space-y-5"
     x-data="{
        copy(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.$dispatch('toast', { msg: 'Copiado para a área de transferência!' });
            });
        }
     }"
     @toast.window="const t = document.getElementById('ai-toast'); if (t) { t.textContent = $event.detail.msg; t.classList.remove('opacity-0'); setTimeout(() => t.classList.add('opacity-0'), 2500); }">

    {{-- Toast --}}
    <div id="ai-toast"
         class="pointer-events-none fixed left-1/2 top-6 z-[100] -translate-x-1/2 rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white opacity-0 shadow-lg transition-opacity dark:bg-white dark:text-slate-900">
    </div>

    {{-- ============================================================
         Hero
         ============================================================ --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-500 via-brand-600 to-accent p-6 text-white shadow-soft sm:p-8">
        <div class="absolute inset-0 opacity-20"
             style="background-image: radial-gradient(circle at 15% 25%, rgba(255,255,255,.6), transparent 40%), radial-gradient(circle at 85% 75%, rgba(255,255,255,.4), transparent 40%);"></div>
        <div class="relative flex items-start gap-4">
            <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-white/20 backdrop-blur">
                <x-icon name="sparkles" class="h-7 w-7"/>
            </div>
            <div class="min-w-0 flex-1">
                <div class="mb-1 flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider opacity-90">
                    <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span>
                    Assistente IA
                </div>
                <h1 class="font-display text-2xl font-bold sm:text-3xl">Sua carreira turbinada com IA</h1>
                <p class="mt-1 max-w-2xl text-sm opacity-90">
                    8 ferramentas de IA para melhorar currículo, otimizar LinkedIn, treinar entrevistas,
                    responder e-mails profissionais e traçar seu plano de carreira.
                </p>
            </div>
        </div>
    </div>

    {{-- ============================================================
         Grid de abas (pills)
         ============================================================ --}}
    @php
        $tabs = [
            ['key' => 'resume',    'label' => 'Currículo',    'icon' => 'academic',  'color' => 'brand', 'desc' => 'Melhore com ATS score'],
            ['key' => 'cover',     'label' => 'Carta',        'icon' => 'message',   'color' => 'blue',  'desc' => 'Personalizada por vaga'],
            ['key' => 'analyze',   'label' => 'Match Vaga',   'icon' => 'trophy',    'color' => 'amber', 'desc' => 'Compatibilidade + gaps'],
            ['key' => 'suggest',   'label' => 'Vagas p/ mim', 'icon' => 'briefcase', 'color' => 'brand', 'desc' => 'IA acha as ideais'],
            ['key' => 'linkedin',  'label' => 'LinkedIn',     'icon' => 'user',      'color' => 'blue',  'desc' => 'Headline + Sobre'],
            ['key' => 'interview', 'label' => 'Entrevista',   'icon' => 'mic',       'color' => 'accent','desc' => '5 perguntas + dicas'],
            ['key' => 'email',     'label' => 'E-mail',       'icon' => 'message',   'color' => 'blue',  'desc' => 'Resposta profissional'],
            ['key' => 'career',    'label' => 'Carreira',     'icon' => 'sparkles',  'color' => 'accent','desc' => 'Plano personalizado'],
        ];
        $colorClasses = [
            'brand'  => 'bg-brand-500/10 text-brand-600',
            'blue'   => 'bg-blue-500/10 text-blue-600',
            'amber'  => 'bg-amber-500/10 text-amber-600',
            'accent' => 'bg-accent/10 text-accent',
        ];
    @endphp

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        @foreach ($tabs as $t)
            @php $active = $tab === $t['key']; @endphp
            <button wire:click="setTab('{{ $t['key'] }}')" type="button"
                    class="group flex items-start gap-2 rounded-2xl border p-3 text-left transition
                           {{ $active
                               ? 'border-brand-500 bg-brand-50 shadow-soft dark:bg-brand-500/10'
                               : 'border-slate-100 bg-white hover:border-brand-300 hover:shadow-soft dark:border-slate-800 dark:bg-slate-900' }}">
                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg {{ $colorClasses[$t['color']] ?? $colorClasses['brand'] }}">
                    <x-icon :name="$t['icon']" class="h-4 w-4"/>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold">{{ $t['label'] }}</p>
                    <p class="truncate text-[10px] text-slate-500">{{ $t['desc'] }}</p>
                </div>
            </button>
        @endforeach
    </div>

    {{-- Alertas globais --}}
    @if ($errorMessage)
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
            {{ $errorMessage }}
        </div>
    @endif
    @if (session('success'))
        <div class="rounded-2xl border border-brand-200 bg-brand-50 p-4 text-sm text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- ============================================================
         ABA: MELHORAR CURRÍCULO
         ============================================================ --}}
    @if ($tab === 'resume')
        <section class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 sm:p-6">
            <div class="mb-4 flex items-center gap-2">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                    <x-icon name="academic" class="h-4 w-4"/>
                </span>
                <div>
                    <h2 class="font-display font-bold">Gerador de Currículo com IA</h2>
                    <p class="text-xs text-slate-500">Preencha os campos abaixo e a IA vai criar um CV profissional otimizado para ATS.</p>
                </div>
            </div>

            {{-- =============== VAGA ALVO =============== --}}
            <div class="mb-4">
                <label class="mb-1 block text-xs font-medium text-slate-500">Vaga alvo <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="resumeTarget" class="input"
                       placeholder="Ex: Analista de Marketing Pleno, Consultor de Vendas, Desenvolvedor Full Stack...">
            </div>

            {{-- =============== FOTO =============== --}}
            <div class="mb-4 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50" x-data="resumePhotoUploader()">
                <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">📸 Foto no currículo</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="setPhotoOption('none')"
                            class="rounded-lg border px-3 py-2 text-xs transition
                                   {{ $resumePhotoOption === 'none' ? 'border-brand-500 bg-brand-500 text-white font-bold' : 'border-slate-200 hover:border-brand-300 dark:border-slate-700' }}">
                        🚫 Sem foto
                    </button>
                    @if (auth()->user()?->avatar_url)
                        <button type="button" wire:click="setPhotoOption('profile')"
                                class="rounded-lg border px-3 py-2 text-xs transition
                                       {{ $resumePhotoOption === 'profile' ? 'border-brand-500 bg-brand-500 text-white font-bold' : 'border-slate-200 hover:border-brand-300 dark:border-slate-700' }}">
                            👤 Foto do perfil
                        </button>
                    @endif
                    <button type="button" wire:click="setPhotoOption('upload')"
                            class="rounded-lg border px-3 py-2 text-xs transition
                                   {{ $resumePhotoOption === 'upload' ? 'border-brand-500 bg-brand-500 text-white font-bold' : 'border-slate-200 hover:border-brand-300 dark:border-slate-700' }}">
                        📎 Enviar nova foto
                    </button>
                </div>

                @if ($resumePhotoOption === 'upload')
                    <div class="mt-3">
                        <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                               @change="upload($event.target.files[0])" x-ref="photoInput">
                        <button type="button" @click="$refs.photoInput.click()"
                                class="btn-ghost text-xs">
                            <template x-if="!loading">
                                <span x-text="path ? '✅ Foto carregada. Clique pra trocar.' : '📎 Escolher arquivo (JPG/PNG, máx 2MB)'"></span>
                            </template>
                            <template x-if="loading">
                                <span class="flex items-center gap-2">
                                    <span class="h-3 w-3 animate-spin rounded-full border-2 border-brand-500/30 border-t-brand-500"></span>
                                    Enviando...
                                </span>
                            </template>
                        </button>
                        <template x-if="error">
                            <p class="mt-2 text-xs text-rose-600" x-text="'⚠️ ' + error"></p>
                        </template>
                    </div>
                @elseif ($resumePhotoOption === 'profile' && auth()->user()?->avatar_url)
                    <div class="mt-3 flex items-center gap-3">
                        <img src="{{ auth()->user()->avatar_url }}" alt="" class="h-14 w-14 rounded-full object-cover ring-2 ring-brand-500/40">
                        <p class="text-xs text-slate-500">Sua foto de perfil será usada no CV.</p>
                    </div>
                @endif
            </div>

            <script>
                function resumePhotoUploader() {
                    return {
                        loading: false,
                        error: '',
                        path: @js($resumePhotoPath ?? ''),
                        async upload(file) {
                            if (!file) return;
                            this.error = '';
                            this.loading = true;
                            try {
                                const fd = new FormData();
                                fd.append('photo', file);
                                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                                const r = await fetch(@json(route('ai.resume-photo')), {
                                    method: 'POST',
                                    credentials: 'same-origin',
                                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                                    body: fd,
                                });
                                const data = await r.json().catch(() => ({}));
                                if (!r.ok || !data.ok) {
                                    this.error = data.msg || 'Falha no upload. Tente outra imagem.';
                                    return;
                                }
                                this.path = data.path;
                                // Empurra path pro Livewire sem re-render pesado
                                const root = this.$el.closest('[wire\\:id]');
                                if (root && window.Livewire) {
                                    const comp = window.Livewire.find(root.getAttribute('wire:id'));
                                    comp?.set('resumePhotoPath', data.path);
                                }
                            } catch (e) {
                                this.error = 'Erro de rede: ' + e.message;
                            } finally {
                                this.loading = false;
                            }
                        }
                    }
                }
            </script>

            {{-- =============== DADOS PESSOAIS =============== --}}
            <div class="mb-4 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50">
                <p class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500">👤 Dados pessoais</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-[11px] font-medium text-slate-500">Nome completo <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.blur="resumeContact.full_name" class="input" placeholder="Ex: João da Silva">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-500">Cargo/Título</label>
                        <input type="text" wire:model.blur="resumeContact.role_title" class="input" placeholder="Ex: Consultor de Vendas">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-500">Telefone</label>
                        <input type="text" wire:model.blur="resumeContact.phone" class="input" placeholder="(11) 99999-9999">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-500">E-mail</label>
                        <input type="email" wire:model.blur="resumeContact.email" class="input" placeholder="voce@email.com">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-500">Data de nascimento</label>
                        <input type="text" wire:model.blur="resumeContact.birth_date" class="input" placeholder="DD/MM/AAAA">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-[11px] font-medium text-slate-500">Endereço</label>
                        <input type="text" wire:model.blur="resumeContact.address" class="input" placeholder="Rua, número, bairro, cidade/UF, CEP">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-500">LinkedIn</label>
                        <input type="text" wire:model.blur="resumeContact.linkedin" class="input" placeholder="linkedin.com/in/seuperfil">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-500">Portfólio/Site</label>
                        <input type="text" wire:model.blur="resumeContact.portfolio" class="input" placeholder="seusite.com">
                    </div>
                </div>
            </div>

            {{-- =============== OBJETIVO & RESUMO =============== --}}
            <div class="mb-4 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50">
                <p class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500">✨ Objetivo & Resumo (opcional — IA gera se deixar vazio)</p>
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-500">Objetivo profissional</label>
                    <textarea wire:model.blur="resumeObjective" rows="2" class="input text-xs"
                              placeholder="Ex: Atuar como Consultor de Vendas aplicando minhas habilidades de negociação..."></textarea>
                </div>
                <div class="mt-2">
                    <label class="mb-1 block text-[11px] font-medium text-slate-500">Resumo profissional</label>
                    <textarea wire:model.blur="resumeSummary" rows="3" class="input text-xs"
                              placeholder="Ex: Profissional com 5 anos de experiência em vendas B2B, especializado em CRM e negociação com grandes contas..."></textarea>
                </div>
            </div>

            {{-- =============== EXPERIÊNCIAS =============== --}}
            <div class="mb-4 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">💼 Experiências profissionais</p>
                    <button type="button" wire:click="addExperience" class="btn-ghost text-xs">➕ Adicionar</button>
                </div>
                @forelse ($resumeExperiences as $i => $exp)
                    <div class="mb-3 rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-slate-500">Experiência {{ $i + 1 }}</span>
                            <button type="button" wire:click="removeExperience({{ $i }})" class="text-xs text-rose-500 hover:underline">✕ Remover</button>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-3">
                            <input type="text" wire:model.blur="resumeExperiences.{{ $i }}.role" class="input text-xs" placeholder="Cargo">
                            <input type="text" wire:model.blur="resumeExperiences.{{ $i }}.company" class="input text-xs" placeholder="Empresa">
                            <input type="text" wire:model.blur="resumeExperiences.{{ $i }}.period" class="input text-xs" placeholder="MM/AAAA – atual">
                        </div>
                        <textarea wire:model.blur="resumeExperiences.{{ $i }}.raw_activities" rows="3" class="input mt-2 text-xs"
                                  placeholder="Descreva o que fazia (uma atividade por linha). Ex.:&#10;Atendia 50 clientes por dia&#10;Superei metas em 30%&#10;Coordenava equipe de 5 pessoas&#10;A IA vai transformar em bullets profissionais."></textarea>
                    </div>
                @empty
                    <p class="rounded-lg border border-dashed border-slate-300 p-4 text-center text-xs text-slate-500 dark:border-slate-700">
                        Nenhuma experiência adicionada. Clique em <b>➕ Adicionar</b> pra começar.
                    </p>
                @endforelse
            </div>

            {{-- =============== FORMAÇÃO =============== --}}
            <div class="mb-4 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">🎓 Formação acadêmica</p>
                    <button type="button" wire:click="addEducation" class="btn-ghost text-xs">➕ Adicionar</button>
                </div>
                @forelse ($resumeEducations as $i => $ed)
                    <div class="mb-2 grid items-end gap-2 sm:grid-cols-[1fr_1fr_120px_auto]">
                        <input type="text" wire:model.blur="resumeEducations.{{ $i }}.degree" class="input text-xs" placeholder="Curso ou grau">
                        <input type="text" wire:model.blur="resumeEducations.{{ $i }}.institution" class="input text-xs" placeholder="Instituição">
                        <input type="text" wire:model.blur="resumeEducations.{{ $i }}.period" class="input text-xs" placeholder="AAAA – AAAA">
                        <button type="button" wire:click="removeEducation({{ $i }})" class="text-xs text-rose-500 hover:underline">✕</button>
                    </div>
                @empty
                    <p class="rounded-lg border border-dashed border-slate-300 p-4 text-center text-xs text-slate-500 dark:border-slate-700">
                        Nenhuma formação adicionada.
                    </p>
                @endforelse
            </div>

            {{-- =============== SKILLS =============== --}}
            <div class="mb-4 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50">
                <p class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-500">🛠️ Habilidades</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-500">Técnicas (hard skills)</label>
                        <input type="text" wire:model.blur="resumeHardSkills" class="input text-xs"
                               placeholder="Excel avançado, CRM Salesforce, SQL, Photoshop">
                        <p class="mt-1 text-[10px] text-slate-400">Separe por vírgula.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-medium text-slate-500">Comportamentais (soft skills)</label>
                        <input type="text" wire:model.blur="resumeSoftSkills" class="input text-xs"
                               placeholder="Comunicação, negociação, liderança">
                        <p class="mt-1 text-[10px] text-slate-400">Separe por vírgula.</p>
                    </div>
                </div>
            </div>

            {{-- =============== IDIOMAS =============== --}}
            <div class="mb-4 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">🌐 Idiomas</p>
                    <button type="button" wire:click="addLanguage" class="btn-ghost text-xs">➕ Adicionar</button>
                </div>
                @forelse ($resumeLanguages as $i => $l)
                    <div class="mb-2 grid items-end gap-2 sm:grid-cols-[1fr_1fr_auto]">
                        <input type="text" wire:model.blur="resumeLanguages.{{ $i }}.language" class="input text-xs" placeholder="Idioma">
                        <input type="text" wire:model.blur="resumeLanguages.{{ $i }}.level" class="input text-xs" placeholder="Nível (básico, intermediário, avançado)">
                        <button type="button" wire:click="removeLanguage({{ $i }})" class="text-xs text-rose-500 hover:underline">✕</button>
                    </div>
                @empty
                    <p class="rounded-lg border border-dashed border-slate-300 p-4 text-center text-xs text-slate-500 dark:border-slate-700">
                        Nenhum idioma adicionado.
                    </p>
                @endforelse
            </div>

            {{-- =============== CERTIFICAÇÕES =============== --}}
            <div class="mb-4 rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/50">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">📜 Certificações</p>
                    <button type="button" wire:click="addCertification" class="btn-ghost text-xs">➕ Adicionar</button>
                </div>
                @forelse ($resumeCertifications as $i => $c)
                    <div class="mb-2 grid items-end gap-2 sm:grid-cols-[1fr_1fr_100px_auto]">
                        <input type="text" wire:model.blur="resumeCertifications.{{ $i }}.name" class="input text-xs" placeholder="Nome">
                        <input type="text" wire:model.blur="resumeCertifications.{{ $i }}.issuer" class="input text-xs" placeholder="Emissor">
                        <input type="text" wire:model.blur="resumeCertifications.{{ $i }}.year" class="input text-xs" placeholder="Ano">
                        <button type="button" wire:click="removeCertification({{ $i }})" class="text-xs text-rose-500 hover:underline">✕</button>
                    </div>
                @empty
                    <p class="rounded-lg border border-dashed border-slate-300 p-4 text-center text-xs text-slate-500 dark:border-slate-700">
                        Nenhuma certificação adicionada.
                    </p>
                @endforelse
            </div>

            {{-- =============== BOTÃO GERAR =============== --}}
            <button wire:click="generateResume" wire:loading.attr="disabled" wire:target="generateResume"
                    class="btn-primary mt-2 w-full">
                <span wire:loading.remove wire:target="generateResume">✨ Gerar meu currículo com IA</span>
                <span wire:loading wire:target="generateResume" class="flex items-center justify-center gap-2">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    A IA está criando seu CV...
                </span>
            </button>

            {{-- =============== RESULTADO =============== --}}
            @if (! empty($resumeResult) && ! empty($resumeResult['resume']))
                <div class="mt-6 space-y-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                    @php $ats = (int) ($resumeResult['ats_score'] ?? 0); @endphp
                    <div class="flex items-center gap-3">
                        <div class="relative grid h-16 w-16 place-items-center rounded-2xl
                                    {{ $ats >= 80 ? 'bg-brand-500/10 text-brand-700' : ($ats >= 60 ? 'bg-amber-500/10 text-amber-700' : 'bg-rose-500/10 text-rose-700') }}">
                            <span class="font-display text-2xl font-bold">{{ $ats }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold">ATS Score estimado</p>
                            <p class="text-xs text-slate-500">
                                @if ($ats >= 80) Excelente! Passa em qualquer filtro automático.
                                @elseif ($ats >= 60) Bom. Ajustes finos podem levar você a 90+.
                                @else Precisa melhorar palavras-chave e estrutura.
                                @endif
                            </p>
                        </div>
                    </div>

                    @if (! empty($resumeResult['suggestions']))
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">💡 Sugestões da IA</p>
                            <ul class="space-y-1 text-sm">
                                @foreach ($resumeResult['suggestions'] as $sug)
                                    <li class="flex gap-2">
                                        <span class="text-brand-500">→</span>
                                        <span>{{ $sug }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">📄 Currículo pronto</p>
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" @click="copy(@js($resumeResult['final_markdown'] ?? ''))"
                                        class="btn-ghost text-xs">📋 Copiar texto</button>
                                <button type="button" wire:click="downloadResumePdf"
                                        wire:loading.attr="disabled" wire:target="downloadResumePdf"
                                        class="btn-primary text-xs !py-1.5 !px-3">
                                    <span wire:loading.remove wire:target="downloadResumePdf">📄 Baixar PDF</span>
                                    <span wire:loading wire:target="downloadResumePdf">Gerando...</span>
                                </button>
                            </div>
                        </div>

                        {{-- Seletor de template do PDF --}}
                        <div class="mb-3 flex flex-wrap gap-1.5 text-[11px]">
                            <span class="self-center text-slate-500">Template:</span>
                            @foreach ([
                                'classic'  => '📜 Clássico',
                                'modern'   => '💼 Moderno',
                                'creative' => '🎨 Criativo',
                            ] as $key => $label)
                                <button type="button" wire:click="setResumeTemplate('{{ $key }}')"
                                        class="rounded-full border px-2.5 py-1 transition
                                               {{ $resumeTemplate === $key
                                                    ? 'border-brand-500 bg-brand-500 text-white font-bold'
                                                    : 'border-slate-200 text-slate-600 hover:border-brand-300 dark:border-slate-700 dark:text-slate-300' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>

                        <div class="max-h-96 overflow-y-auto rounded-xl bg-slate-50 p-4 text-sm whitespace-pre-line dark:bg-slate-800">
                            {{ $resumeResult['final_markdown'] ?? '' }}
                        </div>
                    </div>
                </div>
            @endif
        </section>

    {{-- ============================================================
         ABA: CARTA DE APRESENTAÇÃO
         ============================================================ --}}
    @elseif ($tab === 'cover')
        <section class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 sm:p-6">
            <div class="mb-4 flex items-center gap-2">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-blue-500/10 text-blue-600">
                    <x-icon name="message" class="h-4 w-4"/>
                </span>
                <div>
                    <h2 class="font-display font-bold">Carta de Apresentação</h2>
                    <p class="text-xs text-slate-500">Carta personalizada para cada vaga com seu perfil como base.</p>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Cargo / Vaga <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="coverJobTitle" class="input"
                           placeholder="Ex: Analista de RH Sênior na Ambev, Product Designer no iFood..." />
                    <p class="mt-1 text-[10px] text-slate-400">Pode ser qualquer empresa — não precisa estar cadastrada aqui.</p>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Descrição da vaga (opcional)</label>
                    <textarea wire:model="coverJobDescription" rows="5" class="input font-mono text-xs"
                              placeholder="Cole aqui a descrição real da vaga se tiver — responsabilidades, requisitos, benefícios. Quanto mais detalhes, mais personalizada a carta."></textarea>
                </div>
            </div>

            <button wire:click="generateCover" wire:loading.attr="disabled" wire:target="generateCover"
                    class="btn-primary mt-4 w-full">
                <span wire:loading.remove wire:target="generateCover">✍️ Gerar carta</span>
                <span wire:loading wire:target="generateCover" class="flex items-center justify-center gap-2">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    Escrevendo...
                </span>
            </button>

            @if ($coverResult)
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Carta gerada</p>
                        <button type="button" @click="copy(@js($coverResult))"
                                class="btn-ghost text-xs">📋 Copiar</button>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm whitespace-pre-line dark:border-slate-800 dark:bg-slate-800">
                        {{ $coverResult }}
                    </div>
                </div>
            @else
                <div class="mt-4 rounded-xl border border-dashed border-slate-200 p-4 text-center text-xs text-slate-500 dark:border-slate-700">
                    💡 Selecione uma vaga e a IA gera uma carta persuasiva baseada no SEU perfil e na vaga escolhida.
                </div>
            @endif
        </section>

    {{-- ============================================================
         ABA: ANALISAR VAGA
         ============================================================ --}}
    @elseif ($tab === 'analyze')
        <section class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 sm:p-6">
            <div class="mb-4 flex items-center gap-2">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-amber-500/10 text-amber-600">
                    <x-icon name="trophy" class="h-4 w-4"/>
                </span>
                <div>
                    <h2 class="font-display font-bold">Análise de Compatibilidade</h2>
                    <p class="text-xs text-slate-500">Veja o quanto seu perfil combina com uma vaga específica.</p>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Cargo / Vaga <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="analyzeJobTitle" class="input"
                           placeholder="Ex: Data Scientist Pleno no Mercado Livre, Gerente de Marketing na Magazine Luiza..." />
                    <p class="mt-1 text-[10px] text-slate-400">Pode ser qualquer vaga que você viu em outros sites — cole os detalhes abaixo.</p>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Descrição da vaga (opcional, mas recomendado)</label>
                    <textarea wire:model="analyzeJobDescription" rows="5" class="input font-mono text-xs"
                              placeholder="Cole aqui os requisitos, responsabilidades e diferenciais da vaga. Quanto mais informação, mais precisa a análise de compatibilidade."></textarea>
                </div>
            </div>

            <button wire:click="analyzeJob" wire:loading.attr="disabled" wire:target="analyzeJob"
                    class="btn-primary mt-4 w-full">
                <span wire:loading.remove wire:target="analyzeJob">🎯 Analisar compatibilidade</span>
                <span wire:loading wire:target="analyzeJob" class="flex items-center justify-center gap-2">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    Comparando com seu perfil...
                </span>
            </button>

            @if (! empty($analyzeResult))
                @php $m = (int) ($analyzeResult['match'] ?? 0); @endphp
                <div class="mt-6 space-y-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <div class="flex items-center gap-4">
                        <div class="relative h-20 w-20">
                            <svg class="h-20 w-20 -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="15.915" fill="none" stroke="currentColor"
                                        class="text-slate-200 dark:text-slate-700" stroke-width="3"/>
                                <circle cx="18" cy="18" r="15.915" fill="none" stroke="currentColor"
                                        class="{{ $m >= 70 ? 'text-brand-500' : ($m >= 50 ? 'text-amber-500' : 'text-rose-500') }}"
                                        stroke-width="3" stroke-linecap="round"
                                        stroke-dasharray="{{ $m }}, 100"/>
                            </svg>
                            <div class="absolute inset-0 grid place-items-center">
                                <span class="font-display text-xl font-bold">{{ $m }}%</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Compatibilidade com a vaga</p>
                            <p class="text-xs text-slate-500">
                                @if ($m >= 70) Ótimo match! Vale a pena aplicar.
                                @elseif ($m >= 50) Match razoável. Trabalhe os gaps antes.
                                @else Match baixo. Considere focar em outras vagas.
                                @endif
                            </p>
                        </div>
                    </div>

                    @if (! empty($analyzeResult['strengths']))
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-brand-600">✅ Pontos fortes</p>
                            <ul class="space-y-1 text-sm">
                                @foreach ($analyzeResult['strengths'] as $s)
                                    <li class="flex gap-2"><span class="text-brand-500">✓</span><span>{{ $s }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (! empty($analyzeResult['gaps']))
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-rose-600">⚠️ Gaps</p>
                            <ul class="space-y-1 text-sm">
                                @foreach ($analyzeResult['gaps'] as $g)
                                    <li class="flex gap-2"><span class="text-rose-500">→</span><span>{{ $g }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (! empty($analyzeResult['advice']))
                        <div class="rounded-xl bg-brand-50 p-3 text-sm dark:bg-brand-500/10">
                            <strong class="text-brand-700 dark:text-brand-300">💡 Conselho:</strong>
                            {{ $analyzeResult['advice'] }}
                        </div>
                    @endif
                </div>
            @endif
        </section>

    {{-- ============================================================
         ABA: SUGERIR VAGAS
         ============================================================ --}}
    @elseif ($tab === 'suggest')
        <section class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 sm:p-6">
            <div class="mb-4 flex items-center gap-2">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-500/10 text-brand-600">
                    <x-icon name="briefcase" class="h-4 w-4"/>
                </span>
                <div>
                    <h2 class="font-display font-bold">Vagas Perfeitas Para Você</h2>
                    <p class="text-xs text-slate-500">A IA analisa seu perfil e te mostra as 10 vagas mais compatíveis.</p>
                </div>
            </div>

            <button wire:click="suggest" wire:loading.attr="disabled" wire:target="suggest"
                    class="btn-primary w-full">
                <span wire:loading.remove wire:target="suggest">🔮 Ver vagas para mim</span>
                <span wire:loading wire:target="suggest" class="flex items-center justify-center gap-2">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    Buscando as melhores...
                </span>
            </button>

            @if (! empty($suggestions))
                <ul class="mt-4 space-y-2">
                    @foreach ($suggestions as $s)
                        @php $m = (int) $s['match']; @endphp
                        <li class="rounded-xl border border-slate-100 p-3 transition hover:border-brand-500 dark:border-slate-800">
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('jobs.show', $s['job']) }}"
                                   class="min-w-0 flex-1">
                                    <p class="truncate font-semibold hover:text-brand-600">{{ $s['job']->title }}</p>
                                    <p class="truncate text-xs text-slate-500">
                                        {{ $s['job']->companyProfile?->legal_name ?? 'Empresa' }}
                                    </p>
                                </a>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-bold
                                             {{ $m >= 70 ? 'bg-brand-500/10 text-brand-700' : ($m >= 50 ? 'bg-amber-500/10 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $m }}% match
                                </span>
                            </div>
                            @if ($s['reason'])
                                <p class="mt-2 text-xs italic text-slate-600 dark:text-slate-400">
                                    💡 {{ $s['reason'] }}
                                </p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

    {{-- ============================================================
         ABA: OTIMIZAR LINKEDIN
         ============================================================ --}}
    @elseif ($tab === 'linkedin')
        <section class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 sm:p-6">
            <div class="mb-4 flex items-center gap-2">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-blue-500/10 text-blue-600">
                    <x-icon name="user" class="h-4 w-4"/>
                </span>
                <div>
                    <h2 class="font-display font-bold">Otimizar LinkedIn</h2>
                    <p class="text-xs text-slate-500">Headline + Sobre com palavras-chave que recrutadores buscam.</p>
                </div>
            </div>

            <label class="mb-1 block text-xs font-medium text-slate-500">Vaga alvo (opcional)</label>
            <input type="text" wire:model="linkedinTarget" class="input"
                   placeholder="Ex: Analista de Dados Sênior">

            <button wire:click="optimizeLinkedIn" wire:loading.attr="disabled" wire:target="optimizeLinkedIn"
                    class="btn-primary mt-4 w-full">
                <span wire:loading.remove wire:target="optimizeLinkedIn">🚀 Otimizar meu LinkedIn</span>
                <span wire:loading wire:target="optimizeLinkedIn" class="flex items-center justify-center gap-2">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    Escrevendo texto vencedor...
                </span>
            </button>

            @if (! empty($linkedinResult))
                <div class="mt-6 space-y-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                    @if (! empty($linkedinResult['headline']))
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Headline sugerido</p>
                                <button type="button" @click="copy(@js($linkedinResult['headline']))"
                                        class="btn-ghost text-xs">📋</button>
                            </div>
                            <div class="rounded-xl bg-blue-50 p-3 text-sm font-semibold dark:bg-blue-500/10">
                                {{ $linkedinResult['headline'] }}
                            </div>
                        </div>
                    @endif

                    @if (! empty($linkedinResult['about']))
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Sobre (bio)</p>
                                <button type="button" @click="copy(@js($linkedinResult['about']))"
                                        class="btn-ghost text-xs">📋</button>
                            </div>
                            <div class="max-h-96 overflow-y-auto rounded-xl bg-slate-50 p-4 text-sm whitespace-pre-line dark:bg-slate-800">
                                {{ $linkedinResult['about'] }}
                            </div>
                        </div>
                    @endif

                    @if (! empty($linkedinResult['keywords']))
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">🔑 Palavras-chave</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($linkedinResult['keywords'] as $k)
                                    <span class="rounded-full bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-700">
                                        {{ $k }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <button wire:click="saveLinkedInToProfile"
                            class="btn-primary w-full !bg-brand-500 hover:!bg-brand-600">
                        💾 Aplicar no meu perfil do SocialJobs
                    </button>
                </div>
            @endif
        </section>

    {{-- ============================================================
         ABA: SIMULADOR DE ENTREVISTA
         ============================================================ --}}
    @elseif ($tab === 'interview')
        <section class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 sm:p-6">
            <div class="mb-4 flex items-center gap-2">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-accent/10 text-accent">
                    <x-icon name="mic" class="h-4 w-4"/>
                </span>
                <div>
                    <h2 class="font-display font-bold">Simulador de Entrevista</h2>
                    <p class="text-xs text-slate-500">5 perguntas prováveis + dica de como responder cada uma.</p>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Cargo / Vaga <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="interviewJobTitle" class="input"
                           placeholder="Ex: Analista de Marketing Pleno na Vivo, Desenvolvedor Backend na Nubank, Gerente de Loja..." />
                    <p class="mt-1 text-[10px] text-slate-400">Pode ser qualquer empresa — não precisa estar cadastrada na plataforma.</p>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Descrição da vaga (opcional)</label>
                    <textarea wire:model="interviewJobDescription" rows="5" class="input font-mono text-xs"
                              placeholder="Cole aqui a descrição da vaga se tiver — quanto mais detalhes você fornecer (responsabilidades, requisitos, senioridade, stack), mais precisas as perguntas serão."></textarea>
                </div>
            </div>

            <button wire:click="simulateInterview" wire:loading.attr="disabled" wire:target="simulateInterview"
                    class="btn-primary mt-4 w-full">
                <span wire:loading.remove wire:target="simulateInterview">🎤 Gerar perguntas prováveis</span>
                <span wire:loading wire:target="simulateInterview" class="flex items-center justify-center gap-2">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    Pensando como recrutador...
                </span>
            </button>

            @if (! empty($interviewResult))
                <ol class="mt-4 space-y-3">
                    @foreach ($interviewResult as $i => $q)
                        @php
                            $catColor = match ($q['category']) {
                                'comportamental' => 'bg-brand-500/10 text-brand-700',
                                'tecnica'        => 'bg-blue-500/10 text-blue-700',
                                'cultura'        => 'bg-accent/10 text-accent',
                                default          => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <li class="rounded-xl border border-slate-100 p-3 dark:border-slate-800">
                            <div class="flex items-start gap-2">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-slate-900 text-xs font-bold text-white dark:bg-white dark:text-slate-900">
                                    {{ $i + 1 }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold">{{ $q['question'] }}</p>
                                    <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-medium uppercase {{ $catColor }}">
                                        {{ $q['category'] }}
                                    </span>
                                    @if ($q['tip'])
                                        <p class="mt-2 rounded-lg bg-amber-50 p-2 text-xs text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
                                            💡 <strong>Como responder:</strong> {{ $q['tip'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

    {{-- ============================================================
         ABA: RESPONDER E-MAIL
         ============================================================ --}}
    @elseif ($tab === 'email')
        <section class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 sm:p-6">
            <div class="mb-4 flex items-center gap-2">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-blue-500/10 text-blue-600">
                    <x-icon name="message" class="h-4 w-4"/>
                </span>
                <div>
                    <h2 class="font-display font-bold">Responder E-mail Profissional</h2>
                    <p class="text-xs text-slate-500">Cole o e-mail que recebeu e a IA sugere uma resposta cordial.</p>
                </div>
            </div>

            <label class="mb-1 block text-xs font-medium text-slate-500">Sua intenção</label>
            <select wire:model="emailIntention" class="input">
                <option value="aceitar">Aceitar / concordar</option>
                <option value="recusar">Recusar educadamente</option>
                <option value="negociar">Negociar termos</option>
                <option value="esclarecer">Pedir esclarecimentos</option>
                <option value="agradecer">Agradecer sem se comprometer</option>
            </select>

            <label class="mb-1 mt-3 block text-xs font-medium text-slate-500">E-mail recebido</label>
            <textarea wire:model="emailIncoming" rows="8" class="input font-mono text-xs"
                      placeholder="Cole aqui o texto do e-mail que você recebeu..."></textarea>

            <button wire:click="replyEmail" wire:loading.attr="disabled" wire:target="replyEmail"
                    class="btn-primary mt-4 w-full">
                <span wire:loading.remove wire:target="replyEmail">✉️ Gerar resposta</span>
                <span wire:loading wire:target="replyEmail" class="flex items-center justify-center gap-2">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    Redigindo resposta...
                </span>
            </button>

            @if (! empty($emailResult['reply']))
                <div class="mt-6 space-y-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Sua resposta ({{ $emailResult['tone'] ?? 'cordial' }})
                            </p>
                            <button type="button" @click="copy(@js($emailResult['reply']))"
                                    class="btn-ghost text-xs">📋 Copiar</button>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm whitespace-pre-line dark:border-slate-800 dark:bg-slate-800">
                            {{ $emailResult['reply'] }}
                        </div>
                    </div>

                    @if (! empty($emailResult['tips']))
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">💡 Dicas para personalizar</p>
                            <ul class="space-y-1 text-sm">
                                @foreach ($emailResult['tips'] as $tip)
                                    <li class="flex gap-2"><span class="text-brand-500">→</span><span>{{ $tip }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
        </section>

    {{-- ============================================================
         ABA: PLANO DE CARREIRA
         ============================================================ --}}
    @elseif ($tab === 'career')
        <section class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 sm:p-6">
            <div class="mb-4 flex items-center gap-2">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-accent/10 text-accent">
                    <x-icon name="sparkles" class="h-4 w-4"/>
                </span>
                <div>
                    <h2 class="font-display font-bold">Plano de Carreira Personalizado</h2>
                    <p class="text-xs text-slate-500">A IA analisa seu perfil e sugere próximos passos para 6-12 meses.</p>
                </div>
            </div>

            <label class="mb-1 block text-xs font-medium text-slate-500">Objetivo profissional (opcional)</label>
            <input type="text" wire:model="careerGoal" class="input"
                   placeholder="Ex: Virar Tech Lead em 2 anos, mudar de área para Produto, etc.">

            <button wire:click="careerPlan" wire:loading.attr="disabled" wire:target="careerPlan"
                    class="btn-primary mt-4 w-full">
                <span wire:loading.remove wire:target="careerPlan">🗺️ Gerar meu plano de carreira</span>
                <span wire:loading wire:target="careerPlan" class="flex items-center justify-center gap-2">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    Analisando seu perfil e traçando rota...
                </span>
            </button>

            @if (! empty($careerResult))
                <div class="mt-6 space-y-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                    @if (! empty($careerResult['diagnosis']))
                        <div class="rounded-xl bg-brand-50 p-4 text-sm dark:bg-brand-500/10">
                            <p class="mb-1 text-xs font-bold uppercase tracking-wider text-brand-700 dark:text-brand-300">🔍 Diagnóstico</p>
                            <p class="text-slate-700 dark:text-slate-300">{{ $careerResult['diagnosis'] }}</p>
                        </div>
                    @endif

                    @if (! empty($careerResult['next_steps']))
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">🎯 Próximos passos</p>
                            <ol class="space-y-2">
                                @foreach ($careerResult['next_steps'] as $i => $step)
                                    @php
                                        $priorityColor = match ($step['priority']) {
                                            'alta'  => 'bg-rose-500/10 text-rose-700',
                                            'baixa' => 'bg-slate-100 text-slate-600',
                                            default => 'bg-amber-500/10 text-amber-700',
                                        };
                                    @endphp
                                    <li class="rounded-xl border border-slate-100 p-3 dark:border-slate-800">
                                        <div class="flex items-start gap-3">
                                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-500 text-xs font-bold text-white">
                                                {{ $i + 1 }}
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    <p class="font-semibold">{{ $step['title'] }}</p>
                                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-medium uppercase {{ $priorityColor }}">
                                                        {{ $step['priority'] }}
                                                    </span>
                                                </div>
                                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $step['description'] }}</p>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif

                    @if (! empty($careerResult['skills_to_learn']))
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">📚 Habilidades para aprender</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($careerResult['skills_to_learn'] as $skill)
                                    <span class="rounded-full bg-accent/10 px-3 py-1 text-xs font-medium text-accent">
                                        {{ $skill }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (! empty($careerResult['estimated_timeline']))
                        <div class="rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-800">
                            <p class="mb-1 text-xs font-bold uppercase tracking-wider text-slate-500">⏱️ Prazo estimado</p>
                            <p>{{ $careerResult['estimated_timeline'] }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </section>
    @endif
</div>
