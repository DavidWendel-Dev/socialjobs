<?php

use App\Models\CompanyProfile;
use App\Models\User;
use App\Services\BrasilApiService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /** Passo do wizard: 1 = tipo de conta + dados básicos; 2 = dados corporativos (só empresas). */
    public int $step = 1;

    /** candidate | company */
    public string $type = 'candidate';

    // ===== Dados de acesso (etapa 1) =====
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;

    // ===== Dados corporativos (etapa 2) =====
    public string $cnpj = '';
    public string $legal_name = '';     // Razão social
    public string $trade_name = '';     // Nome fantasia
    public string $industry = '';       // Setor / CNAE
    public string $size = '';           // Porte (1-10, 11-50, 51-200, 201-500, 500+)
    public string $website = '';
    public string $phone = '';

    /** Endereço puxado da BrasilAPI (não editável — só exibimos). */
    public array $address = [];

    /** true enquanto a consulta ao BrasilAPI está em andamento. */
    public bool $cnpjLoading = false;

    /** Mensagem de aviso da consulta ao CNPJ. */
    public string $cnpjMessage = '';

    /** Opções pré-definidas de porte da empresa (padrão LinkedIn/Vagas.com). */
    public array $sizeOptions = [
        '1-10'    => '1-10 funcionários',
        '11-50'   => '11-50 funcionários',
        '51-200'  => '51-200 funcionários',
        '201-500' => '201-500 funcionários',
        '501+'    => 'Mais de 500 funcionários',
    ];

    /**
     * Consulta o CNPJ na BrasilAPI e pré-preenche os campos.
     * Chamado automaticamente quando `cnpj` muda (via wire:model.live).
     */
    public function updatedCnpj(): void
    {
        $clean = BrasilApiService::stripCnpj($this->cnpj);

        // Reset mensagens
        $this->cnpjMessage = '';

        // Só consulta quando tiver 14 dígitos
        if (strlen($clean) !== 14) {
            return;
        }

        if (! BrasilApiService::isValidCnpj($clean)) {
            $this->cnpjMessage = 'CNPJ inválido. Verifique os dígitos.';
            return;
        }

        // Verifica se já existe no banco
        if (CompanyProfile::where('cnpj', $clean)->exists()) {
            $this->addError('cnpj', 'Este CNPJ já está cadastrado.');
            return;
        }

        $this->cnpjLoading = true;

        try {
            $data = app(BrasilApiService::class)->lookupCnpj($clean);

            if (! $data) {
                $this->cnpjMessage = 'Não conseguimos localizar este CNPJ na base pública. Você pode preencher os dados manualmente.';
                return;
            }

            $this->legal_name = $data['razao_social'];
            $this->trade_name = $data['nome_fantasia'] ?: $data['razao_social'];
            $this->industry   = $data['cnae_principal'] ?: $this->industry;
            $this->phone      = $data['telefone'] ?: $this->phone;
            $this->address    = $data['address'];

            // Se o nome do usuário na etapa 1 ainda estava vazio, propõe o nome fantasia/razão social
            if (trim($this->name) === '') {
                $this->name = $this->trade_name ?: $this->legal_name;
            }

            $this->cnpjMessage = '✓ Dados carregados! Confira e ajuste se precisar.';
        } catch (\Throwable $e) {
            report($e);
            $this->cnpjMessage = 'Falha ao consultar CNPJ. Continue preenchendo os dados manualmente.';
        } finally {
            $this->cnpjLoading = false;
        }
    }

    /**
     * Vai para a próxima etapa do cadastro (só se etapa 1 estiver válida).
     */
    public function nextStep(): void
    {
        $this->validate([
            'name'                  => ['required', 'string', 'min:2', 'max:120'],
            'email'                 => ['required', 'string', 'lowercase', 'email', 'max:191', 'unique:' . User::class],
            'password'              => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'type'                  => ['required', 'in:candidate,company'],
            'terms'                 => ['accepted'],
        ], [
            'terms.accepted' => 'Você precisa aceitar os termos para criar sua conta.',
        ]);

        // Se for candidato, cria a conta direto
        if ($this->type === 'candidate') {
            $this->register();
            return;
        }

        // Se for empresa, vai para a etapa 2
        $this->step = 2;
    }

    public function backStep(): void
    {
        $this->step = 1;
    }

    /**
     * Cria a conta.
     */
    public function register(): void
    {
        // Revalida etapa 1
        $validated = $this->validate([
            'name'                  => ['required', 'string', 'min:2', 'max:120'],
            'email'                 => ['required', 'string', 'lowercase', 'email', 'max:191', 'unique:' . User::class],
            'password'              => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'type'                  => ['required', 'in:candidate,company'],
            'terms'                 => ['accepted'],
        ]);

        // Se for empresa, valida etapa 2
        if ($this->type === 'company') {
            // Normaliza o CNPJ removendo qualquer máscara (pontos, barras, traços)
            // ANTES de validar tamanho. Sem isso, o input mascarado (18 chars)
            // falha na regra size:14 mesmo tendo os 14 dígitos corretos.
            $this->cnpj = BrasilApiService::stripCnpj($this->cnpj);

            $this->validate([
                'cnpj'       => ['required', 'string', 'size:14'],
                'legal_name' => ['required', 'string', 'max:255'],
                'trade_name' => ['nullable', 'string', 'max:255'],
                'industry'   => ['nullable', 'string', 'max:255'],
                'size'       => ['nullable', 'string', 'in:1-10,11-50,51-200,201-500,501+'],
                'website'    => ['nullable', 'url', 'max:191'],
                'phone'      => ['nullable', 'string', 'max:30'],
            ], [
                'cnpj.size' => 'O CNPJ deve ter 14 dígitos.',
            ]);

            if (! BrasilApiService::isValidCnpj($this->cnpj)) {
                $this->addError('cnpj', 'CNPJ inválido.');
                return;
            }
            if (CompanyProfile::where('cnpj', $this->cnpj)->exists()) {
                $this->addError('cnpj', 'Este CNPJ já está cadastrado.');
                return;
            }
        }

        $user = DB::transaction(function () use ($validated) {
            $userData = [
                'name'     => $this->name,
                'email'    => $this->email,
                'password' => Hash::make($this->password),
                'type'     => $this->type,
                'username' => $this->generateUniqueUsername($this->name),
            ];

            $user = User::create($userData);

            // Cria perfil de empresa se aplicável.
            // OBS: o observer `User::booted()` já cria automaticamente um
            // CompanyProfile básico (com legal_name = user->name) quando
            // type = "company". Aqui usamos `updateOrCreate` para preencher
            // os campos completos que o observer não conhece (CNPJ, endereço,
            // porte, etc.) sem duplicar a linha.
            if ($this->type === 'company') {
                CompanyProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'cnpj'       => $this->cnpj,
                        'legal_name' => $this->legal_name ?: $this->name,
                        'trade_name' => $this->trade_name ?: null,
                        'slug'       => $this->generateUniqueSlug($this->trade_name ?: $this->legal_name ?: $this->name),
                        'industry'   => $this->industry ?: null,
                        'size'       => $this->size ?: null,
                        'website'    => $this->website ?: null,
                        'phone'      => $this->phone ?: null,
                        'address'    => ! empty($this->address) ? $this->address : null,
                    ]
                );
            }

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);

        // Usa URL absoluta para respeitar o sub-path do APP_URL
        // (ex.: http://localhost/SocialJobs/public) — caso contrário
        // o Livewire redirecionaria para /feed sem o prefixo.
        if ($this->type === 'company') {
            $this->redirect(route('feed'), navigate: true);
        } else {
            $this->redirect(route('onboarding'), navigate: true);
        }
    }

    private function generateUniqueUsername(string $name): string
    {
        $base = Str::slug($name) ?: 'usuario';
        $username = $base;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . '-' . $counter;
            $counter++;
        }
        return $username;
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'empresa';
        $slug = $base;
        $counter = 1;
        while (CompanyProfile::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
}; ?>

<div class="space-y-6">
    {{-- ============================================================
         Cabeçalho + progresso
         ============================================================ --}}
    <div>
        <div class="mb-4 flex items-center gap-2">
            {{-- Steps indicator (só aparece se for empresa) --}}
            @if ($type === 'company')
                <div class="flex flex-1 items-center gap-1">
                    <span class="h-1.5 flex-1 rounded-full {{ $step >= 1 ? 'bg-brand-500' : 'bg-slate-200 dark:bg-slate-700' }}"></span>
                    <span class="h-1.5 flex-1 rounded-full {{ $step >= 2 ? 'bg-brand-500' : 'bg-slate-200 dark:bg-slate-700' }}"></span>
                </div>
                <span class="text-xs font-medium text-slate-500">Etapa {{ $step }}/2</span>
            @endif
        </div>

        @if ($step === 1)
            <h1 class="flex items-center gap-2 font-display text-3xl font-bold">
                Crie sua conta grátis
                {{-- Ícone: foguete (decolar/começar) --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                </svg>
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Comece hoje. Sem custo, sem enrolação.
            </p>
        @else
            <h1 class="flex items-center gap-2 font-display text-3xl font-bold">
                Dados da empresa
                {{-- Ícone: prédio corporativo --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                </svg>
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Vamos preencher tudo automaticamente pelo CNPJ.
            </p>
        @endif
    </div>

    {{-- ============================================================
         ETAPA 1: dados de acesso
         ============================================================ --}}
    @if ($step === 1)
        <form wire:submit.prevent="nextStep" class="space-y-4">
            {{-- Tipo de conta --}}
            <div>
                <label class="mb-2 block text-sm font-medium">Sou uma:</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ([
                        'candidate' => ['label' => 'Pessoa',  'desc' => 'Busco oportunidades', 'icon' => 'user'],
                        'company'   => ['label' => 'Empresa', 'desc' => 'Contrato talentos',   'icon' => 'briefcase'],
                    ] as $key => $data)
                        <button type="button"
                                wire:click="$set('type', '{{ $key }}')"
                                class="rounded-2xl border-2 p-3 text-left transition
                                       {{ $type === $key
                                           ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10'
                                           : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600' }}">
                            <div class="flex items-center gap-2">
                                <x-icon :name="$data['icon']"
                                        class="h-5 w-5 {{ $type === $key ? 'text-brand-600' : 'text-slate-400' }}"/>
                                <span class="text-sm font-semibold">{{ $data['label'] }}</span>
                            </div>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $data['desc'] }}</p>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Nome --}}
            <div>
                <label for="name" class="mb-1 block text-sm font-medium">
                    {{ $type === 'company' ? 'Nome do responsável (contato)' : 'Seu nome completo' }}
                </label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <input wire:model="name" id="name" type="text" name="name"
                           required autofocus autocomplete="name"
                           placeholder="{{ $type === 'company' ? 'Ex: Maria (RH)' : 'Ex: João da Silva' }}"
                           class="input pl-10">
                </div>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- E-mail --}}
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">E-mail</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                         stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <path d="m22 7-10 5L2 7"/>
                    </svg>
                    <input wire:model="email" id="email" type="email" name="email"
                           required autocomplete="username"
                           placeholder="{{ $type === 'company' ? 'contato@suaempresa.com' : 'voce@email.com' }}"
                           class="input pl-10">
                </div>
                @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Senha --}}
            <div x-data="{
                    show: false,
                    get strength() {
                        const p = $wire.password || '';
                        let s = 0;
                        if (p.length >= 8) s++;
                        if (/[A-Z]/.test(p)) s++;
                        if (/[0-9]/.test(p)) s++;
                        if (/[^A-Za-z0-9]/.test(p)) s++;
                        return s;
                    },
                    get strengthLabel() { return ['', 'Fraca', 'Ok', 'Boa', 'Forte'][this.strength] || ''; },
                    get strengthColor() { return ['bg-slate-200','bg-rose-500','bg-amber-500','bg-blue-500','bg-brand-500'][this.strength] || 'bg-slate-200'; }
                 }">
                <label for="password" class="mb-1 block text-sm font-medium">Senha</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="password" id="password"
                           :type="show ? 'text' : 'password'" name="password"
                           required autocomplete="new-password"
                           placeholder="Mínimo 8 caracteres"
                           class="input px-10">
                    <button type="button" @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-ink dark:hover:text-white">
                        <svg x-show="!show" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg x-show="show" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>
                        </svg>
                    </button>
                </div>
                {{-- Medidor força --}}
                <div class="mt-2 flex items-center gap-2" x-show="$wire.password && $wire.password.length > 0" x-cloak>
                    <div class="flex flex-1 gap-1">
                        <span class="h-1 flex-1 rounded-full transition-colors" :class="strength >= 1 ? strengthColor : 'bg-slate-200 dark:bg-slate-700'"></span>
                        <span class="h-1 flex-1 rounded-full transition-colors" :class="strength >= 2 ? strengthColor : 'bg-slate-200 dark:bg-slate-700'"></span>
                        <span class="h-1 flex-1 rounded-full transition-colors" :class="strength >= 3 ? strengthColor : 'bg-slate-200 dark:bg-slate-700'"></span>
                        <span class="h-1 flex-1 rounded-full transition-colors" :class="strength >= 4 ? strengthColor : 'bg-slate-200 dark:bg-slate-700'"></span>
                    </div>
                    <span class="text-[10px] font-medium text-slate-500" x-text="strengthLabel"></span>
                </div>
                @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Confirmação --}}
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium">Confirmar senha</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/>
                    </svg>
                    <input wire:model="password_confirmation" id="password_confirmation"
                           type="password" name="password_confirmation"
                           required autocomplete="new-password"
                           placeholder="Repita a senha"
                           class="input pl-10">
                </div>
                @error('password_confirmation') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Termos --}}
            <label class="flex items-start gap-2 cursor-pointer">
                <input wire:model="terms" type="checkbox"
                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                <span class="text-xs text-slate-600 dark:text-slate-300">
                    Concordo com os
                    <a href="{{ route('legal.terms') }}" target="_blank" class="font-medium text-brand-600 hover:underline">Termos de Uso</a>
                    e a
                    <a href="{{ route('legal.privacy') }}" target="_blank" class="font-medium text-brand-600 hover:underline">Política de Privacidade</a>.
                </span>
            </label>
            @error('terms') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror

            {{-- Botão --}}
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="nextStep,register"
                    class="btn-primary w-full !py-3 !text-base">
                <span wire:loading.remove wire:target="nextStep,register">
                    {{ $type === 'company' ? 'Continuar → dados da empresa' : 'Criar conta grátis' }}
                </span>
                <span wire:loading wire:target="nextStep,register" class="flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".3"/>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    Processando...
                </span>
            </button>
        </form>

        {{-- Divisor + link para login --}}
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
            </div>
            <div class="relative flex justify-center text-xs">
                <span class="bg-paper px-3 text-slate-500 dark:bg-ink-dark">ou</span>
            </div>
        </div>

        <p class="text-center text-sm text-slate-600 dark:text-slate-300">
            Já tem uma conta?
            <a href="{{ route('login') }}" wire:navigate class="font-semibold text-brand-600 hover:underline">
                Fazer login
            </a>
        </p>
    @endif

    {{-- ============================================================
         ETAPA 2: dados corporativos (só empresas)
         ============================================================ --}}
    @if ($step === 2)
        <form wire:submit.prevent="register" class="space-y-4">
            {{-- CNPJ (com autocompletar) --}}
            <div x-data="{
                    format(value) {
                        const d = value.replace(/\D/g, '').slice(0, 14);
                        if (d.length <= 2) return d;
                        if (d.length <= 5) return d.slice(0,2) + '.' + d.slice(2);
                        if (d.length <= 8) return d.slice(0,2) + '.' + d.slice(2,5) + '.' + d.slice(5);
                        if (d.length <= 12) return d.slice(0,2) + '.' + d.slice(2,5) + '.' + d.slice(5,8) + '/' + d.slice(8);
                        return d.slice(0,2) + '.' + d.slice(2,5) + '.' + d.slice(5,8) + '/' + d.slice(8,12) + '-' + d.slice(12);
                    }
                 }">
                <label for="cnpj" class="mb-1 block text-sm font-medium">CNPJ</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 20h20"/>
                        <path d="M4 20V8l6-4 6 4v12"/>
                        <path d="M10 20v-6h4v6"/>
                    </svg>
                    <input wire:model.live.debounce.500ms="cnpj"
                           :value="format($wire.cnpj)"
                           @input="$wire.cnpj = $event.target.value.replace(/\D/g, '')"
                           id="cnpj"
                           type="text"
                           inputmode="numeric"
                           placeholder="00.000.000/0000-00"
                           maxlength="18"
                           class="input pl-10 pr-10">

                    {{-- Ícone de status: loading, ok ou nada --}}
                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                        <div wire:loading wire:target="cnpj,updatedCnpj">
                            <svg class="h-4 w-4 animate-spin text-brand-500" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".3"/>
                                <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div wire:loading.remove wire:target="cnpj,updatedCnpj">
                            @if (strlen($cnpj) === 14 && $legal_name)
                                <svg class="h-4 w-4 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 6 9 17l-5-5"/>
                                </svg>
                            @endif
                        </div>
                    </div>
                </div>
                @if ($cnpjMessage)
                    <p class="mt-1 text-xs {{ str_starts_with($cnpjMessage, '✓') ? 'text-brand-600' : 'text-amber-600' }}">
                        {{ $cnpjMessage }}
                    </p>
                @endif
                @error('cnpj') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Razão social --}}
            <div>
                <label for="legal_name" class="mb-1 block text-sm font-medium">
                    Razão social <span class="text-rose-500">*</span>
                </label>
                <input wire:model="legal_name" id="legal_name" type="text"
                       placeholder="Ex: Acme Tecnologia LTDA" class="input">
                @error('legal_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Nome fantasia --}}
            <div>
                <label for="trade_name" class="mb-1 block text-sm font-medium">Nome fantasia</label>
                <input wire:model="trade_name" id="trade_name" type="text"
                       placeholder="Como sua empresa é conhecida" class="input">
                <p class="mt-1 text-xs text-slate-500">Ex: "Acme" (a marca que aparece publicamente)</p>
            </div>

            {{-- Setor / CNAE --}}
            <div>
                <label for="industry" class="mb-1 block text-sm font-medium">Setor de atuação</label>
                <input wire:model="industry" id="industry" type="text"
                       placeholder="Ex: Tecnologia, Saúde, Educação..." class="input">
            </div>

            {{-- Tamanho --}}
            <div>
                <label for="size" class="mb-1 block text-sm font-medium">Porte da empresa</label>
                <select wire:model="size" id="size" class="input">
                    <option value="">Selecione o porte</option>
                    @foreach ($sizeOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Site --}}
            <div>
                <label for="website" class="mb-1 block text-sm font-medium">Site</label>
                <input wire:model="website" id="website" type="url"
                       placeholder="https://www.suaempresa.com" class="input">
                @error('website') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Telefone --}}
            <div>
                <label for="phone" class="mb-1 block text-sm font-medium">Telefone</label>
                <input wire:model="phone" id="phone" type="tel"
                       placeholder="(11) 99999-9999" class="input">
            </div>

            {{-- Endereço (só exibe, veio da BrasilAPI) --}}
            @if (! empty($address['municipio']))
                <div class="rounded-2xl bg-slate-50 p-3 text-xs dark:bg-slate-800">
                    <p class="mb-1 flex items-center gap-1 font-semibold text-slate-600 dark:text-slate-300">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        Endereço registrado
                    </p>
                    <p class="text-slate-500">
                        {{ $address['logradouro'] ?? '' }}
                        @if (! empty($address['numero'])), {{ $address['numero'] }} @endif
                        @if (! empty($address['bairro'])) — {{ $address['bairro'] }} @endif
                        <br>
                        {{ $address['municipio'] ?? '' }}/{{ $address['uf'] ?? '' }}
                        @if (! empty($address['cep'])) — CEP {{ $address['cep'] }} @endif
                    </p>
                </div>
            @endif

            {{-- Botões --}}
            <div class="flex gap-2">
                <button type="button" wire:click="backStep" class="btn-secondary flex-1">
                    ← Voltar
                </button>
                <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="register"
                        class="btn-primary flex-1 !py-3 !text-base">
                    <span wire:loading.remove wire:target="register">Criar empresa</span>
                    <span wire:loading wire:target="register">Criando...</span>
                </button>
            </div>
        </form>
    @endif
</div>
