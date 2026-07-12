<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        // Todos (empresas e candidatos) vão para o feed após login.
        // Empresas podem acessar o dashboard depois pelo menu lateral.
        $defaultRoute = route('feed');

        $this->redirectIntended(default: $defaultRoute, navigate: true);
    }
}; ?>

<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div>
        <h1 class="flex items-center gap-2 font-display text-3xl font-bold">
            Bem-vindo de volta
            {{-- Ícone: mão acenando (waving hand) --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 2.51a2.25 2.25 0 0 1 3.4 2.94l-1.15 1.32M9.75 2.51a2.25 2.25 0 0 0-3.18 3.18l1.06 1.06m11.31 4.24-6.7-6.7a2.25 2.25 0 0 0-3.18 3.18l1.06 1.06M7.63 6.75a2.25 2.25 0 0 0-3.18 3.18l6.71 6.71m0 0a5.5 5.5 0 0 0 7.78-7.78l-1.06-1.06M4.45 9.93a2.25 2.25 0 0 0 0 3.18l4.6 4.6a5.5 5.5 0 0 0 7.78-7.78"/>
            </svg>
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            Entre na sua conta para continuar sua jornada profissional.
        </p>
    </div>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-700 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login" class="space-y-4">
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
                <input wire:model="form.email"
                       id="email"
                       type="email"
                       name="email"
                       required autofocus autocomplete="username"
                       placeholder="voce@email.com"
                       class="input pl-10">
            </div>
            @error('form.email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        {{-- Senha --}}
        <div x-data="{ show: false }">
            <div class="mb-1 flex items-center justify-between">
                <label for="password" class="block text-sm font-medium">Senha</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       wire:navigate
                       class="text-xs font-medium text-brand-600 hover:underline">
                        Esqueceu a senha?
                    </a>
                @endif
            </div>
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                     stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input wire:model="form.password"
                       id="password"
                       :type="show ? 'text' : 'password'"
                       name="password"
                       required autocomplete="current-password"
                       placeholder="••••••••"
                       class="input px-10">
                <button type="button" @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-ink dark:hover:text-white"
                        aria-label="Mostrar/ocultar senha">
                    <svg x-show="!show" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                        <line x1="2" x2="22" y1="2" y2="22"/>
                    </svg>
                </button>
            </div>
            @error('form.password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        {{-- Lembrar de mim --}}
        <label class="flex items-center gap-2 cursor-pointer">
            <input wire:model="form.remember"
                   type="checkbox"
                   name="remember"
                   class="h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500">
            <span class="text-sm text-slate-600 dark:text-slate-300">Lembrar de mim neste dispositivo</span>
        </label>

        {{-- Botão --}}
        <button type="submit"
                wire:loading.attr="disabled"
                wire:target="login"
                class="btn-primary w-full !py-3 !text-base">
            <span wire:loading.remove wire:target="login">Entrar</span>
            <span wire:loading wire:target="login" class="flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".3"/>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                </svg>
                Entrando...
            </span>
        </button>
    </form>

    {{-- Divisor --}}
    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
        </div>
        <div class="relative flex justify-center text-xs">
            <span class="bg-paper px-3 text-slate-500 dark:bg-ink-dark">ou</span>
        </div>
    </div>

    {{-- CTA de cadastro --}}
    <p class="text-center text-sm text-slate-600 dark:text-slate-300">
        Ainda não tem conta?
        <a href="{{ route('register') }}" wire:navigate
           class="font-semibold text-brand-600 hover:underline">
            Cadastre-se grátis
        </a>
    </p>
</div>
