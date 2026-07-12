<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Currículo Digital · SocialJobs' }}</title>

    <!-- Open Graph para bom preview quando compartilhado -->
    <meta property="og:title" content="{{ $title ?? 'Currículo Digital' }}">
    <meta property="og:type" content="profile">
    <meta property="og:site_name" content="SocialJobs">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Estilos otimizados para impressão (usuário aperta Ctrl+P e vira PDF). */
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; }
            a { color: #059669 !important; text-decoration: none; }
            .print-avoid-break { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    {{-- Barra flutuante superior (some no print) --}}
    <div class="no-print sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-3">
            <a href="{{ url('/') }}" class="flex items-center gap-2 font-display text-sm font-bold">
                <span class="grid h-7 w-7 place-items-center rounded-lg bg-gradient-to-br from-brand-500 to-accent text-white">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/>
                    </svg>
                </span>
                <span class="hidden sm:inline">SocialJobs</span>
            </a>

            <div class="flex items-center gap-2">
                <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 dark:bg-white dark:text-slate-900 dark:hover:bg-brand-500 dark:hover:text-white">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    <span class="hidden sm:inline">Exportar PDF</span>
                    <span class="sm:hidden">PDF</span>
                </button>

                <button type="button"
                        x-data="{ copied: false }"
                        @click="navigator.clipboard.writeText(window.location.href); copied = true; setTimeout(() => copied = false, 1500);"
                        class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                    </svg>
                    <span x-text="copied ? 'Copiado!' : 'Compartilhar'"></span>
                </button>
            </div>
        </div>
    </div>

    <main class="mx-auto max-w-4xl px-4 py-6 sm:py-10">
        <div class="cv-page overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
            {{ $slot }}
        </div>
    </main>

    <footer class="no-print mx-auto max-w-4xl px-4 pb-8 text-center text-xs text-slate-500">
        Currículo verificado pela plataforma <a href="{{ url('/') }}" class="font-semibold text-brand-600 hover:underline">SocialJobs</a>
    </footer>
</body>
</html>
