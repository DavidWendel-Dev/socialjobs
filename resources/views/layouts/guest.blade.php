<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SocialJobs') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-ink dark:bg-ink-dark dark:text-slate-100">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- ============================================================
             Lado esquerdo — visual, gradiente e narrativa
             (aparece só em telas grandes)
             ============================================================ --}}
        <aside class="relative hidden overflow-hidden bg-gradient-to-br from-brand-500 via-brand-600 to-accent p-12 text-white lg:flex lg:flex-col lg:justify-between">
            {{-- Padrão de gradientes radiais decorativos --}}
            <div class="absolute inset-0 opacity-30"
                 style="background-image:
                    radial-gradient(circle at 20% 30%, rgba(255,255,255,.6) 0, transparent 40%),
                    radial-gradient(circle at 80% 60%, rgba(255,255,255,.4) 0, transparent 40%),
                    radial-gradient(circle at 60% 90%, rgba(0,0,0,.2) 0, transparent 50%);"></div>

            {{-- Logo --}}
            <a href="{{ route('landing') }}" class="relative z-10 flex items-center gap-3 font-display text-2xl font-bold">
                <img src="/favicon/favicon-96x96.png"
                     alt="SocialJobs"
                     width="44" height="44"
                     class="h-11 w-11 rounded-2xl bg-white/20 backdrop-blur p-1">
                SocialJobs
            </a>

            {{-- Narrativa --}}
            <div class="relative z-10 space-y-6">
                <h1 class="font-display text-4xl font-bold leading-tight">
                    Sua carreira <br>
                    começa com uma <br>
                    <span class="text-white/90 underline decoration-white/40 underline-offset-4">boa oportunidade.</span>
                </h1>
                <p class="max-w-md text-lg text-white/90">
                    Conecte-se com empresas, aprenda com nossa comunidade e conquiste
                    a vaga que combina com você.
                </p>

                {{-- Benefícios com SVG profissional --}}
                <ul class="space-y-3 pt-4">
                    @php
                        $benefits = [
                            [
                                'label' => 'Match inteligente com vagas',
                                'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>',
                            ],
                            [
                                'label' => 'Cursos gratuitos com certificação',
                                'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>',
                            ],
                            [
                                'label' => 'Rede social profissional brasileira',
                                'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>',
                            ],
                            [
                                'label' => 'IA para melhorar seu currículo',
                                'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z"/>',
                            ],
                        ];
                    @endphp
                    @foreach ($benefits as $item)
                        <li class="flex items-center gap-3 text-white/90">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-white/15 backdrop-blur">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                    {!! $item['svg'] !!}
                                </svg>
                            </span>
                            <span>{{ $item['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Rodapé --}}
            <div class="relative z-10 flex items-center gap-2 text-xs text-white/70">
                <span>© {{ date('Y') }} SocialJobs — Feito com carinho no Brasil</span>
                {{-- Bandeira do Brasil em SVG --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-5 rounded-sm shadow-sm" viewBox="0 0 20 14">
                    <rect width="20" height="14" fill="#009c3b"/>
                    <polygon points="10,1.5 18.5,7 10,12.5 1.5,7" fill="#ffdf00"/>
                    <circle cx="10" cy="7" r="2.5" fill="#002776"/>
                </svg>
            </div>
        </aside>

        {{-- ============================================================
             Lado direito — formulário
             ============================================================ --}}
        <main class="flex flex-col justify-center px-6 py-10 sm:px-12 lg:px-16">
            {{-- Logo mobile --}}
            <a href="{{ route('landing') }}"
               class="mb-8 inline-flex items-center gap-2 self-start font-display text-xl font-bold lg:hidden">
                <img src="/favicon/favicon-96x96.png"
                     alt="SocialJobs"
                     width="36" height="36"
                     class="h-9 w-9 rounded-xl">
                SocialJobs
            </a>

            <div class="mx-auto w-full max-w-md">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
