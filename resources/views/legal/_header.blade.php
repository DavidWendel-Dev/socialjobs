@php
    /**
     * Componente compartilhado das páginas legais.
     * Cabeçalho, TOC e footer padronizados.
     *
     * Props esperadas:
     *   $active   - 'terms' | 'privacy' | 'cookies'
     *   $title    - título da página
     *   $subtitle - descrição curta
     *   $updated  - data da última atualização (Y-m-d)
     */
    $tabs = [
        'terms'    => ['label' => 'Termos de Uso',           'route' => 'legal.terms'],
        'privacy'  => ['label' => 'Política de Privacidade', 'route' => 'legal.privacy'],
        'cookies'  => ['label' => 'Cookies',                 'route' => 'legal.cookies'],
    ];
@endphp

<section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-500 via-brand-600 to-accent-500 p-6 sm:p-10 text-white shadow-soft-lg">
    {{-- Decoração --}}
    <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
    <div class="pointer-events-none absolute -left-16 bottom-0 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>

    <div class="relative">
        <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium backdrop-blur">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <span>Gratuito para sempre — candidatos e empresas</span>
        </div>
        <h1 class="font-display text-3xl sm:text-4xl font-bold tracking-tight">
            {{ $title }}
        </h1>
        @isset($subtitle)
            <p class="mt-2 max-w-2xl text-sm sm:text-base text-white/90">{{ $subtitle }}</p>
        @endisset
        @isset($updated)
            <p class="mt-3 text-xs text-white/70">
                Última atualização: {{ \Carbon\Carbon::parse($updated)->translatedFormat('d \d\e F \d\e Y') }}
            </p>
        @endisset
    </div>
</section>

{{-- Tabs --}}
<nav class="mt-6 flex flex-wrap gap-2" aria-label="Navegação das páginas legais">
    @foreach ($tabs as $key => $tab)
        @php $isActive = $active === $key; @endphp
        <a href="{{ route($tab['route']) }}"
           class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition
                  {{ $isActive
                      ? 'bg-brand-500 text-white shadow-soft'
                      : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-brand-50 hover:text-brand-700 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-brand-500/10 dark:hover:text-brand-300' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
