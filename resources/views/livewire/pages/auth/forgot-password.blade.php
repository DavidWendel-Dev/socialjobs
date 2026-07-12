<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink($this->only('email'));

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));
            return;
        }

        $this->reset('email');
        session()->flash('status', __($status));
    }
}; ?>

<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div>
        <h1 class="font-display text-3xl font-bold">Esqueceu a senha? 🔑</h1>
        <p class="mt-2 text-sm text-slate-500">
            Sem problema. Informe seu e-mail e enviaremos um link para você criar uma nova senha.
        </p>
    </div>

    @if (session('status'))
        <div class="rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300">
            ✅ {{ session('status') }}
        </div>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-4">
        <div>
            <label for="email" class="mb-1 block text-sm font-medium">E-mail cadastrado</label>
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                     stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="m22 7-10 5L2 7"/>
                </svg>
                <input wire:model="email"
                       id="email" type="email" name="email"
                       required autofocus
                       placeholder="voce@email.com"
                       class="input pl-10">
            </div>
            @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit"
                wire:loading.attr="disabled"
                class="btn-primary w-full !py-3 !text-base">
            <span wire:loading.remove>Enviar link de redefinição</span>
            <span wire:loading>Enviando...</span>
        </button>
    </form>

    <p class="text-center text-sm text-slate-600 dark:text-slate-300">
        Lembrou da senha?
        <a href="{{ route('login') }}" wire:navigate
           class="font-semibold text-brand-600 hover:underline">
            Voltar para o login
        </a>
    </p>
</div>
