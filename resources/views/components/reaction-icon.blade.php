@props(['type' => 'like', 'size' => 'md', 'animated' => false])

@php
    // Tamanhos em pixels — usa dimensões fixas pra ícones "bolha" ficarem crispy
    $sizes = [
        'xs' => 'h-4 w-4',
        'sm' => 'h-5 w-5',
        'md' => 'h-6 w-6',
        'lg' => 'h-8 w-8',
        'xl' => 'h-10 w-10',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    // ViewBox com respiro (-1..25) evita corte do stroke nas bordas do círculo.
    $vb = '-1 -1 26 26';
    // ID único por instância — evita conflito de <defs> quando várias reações
    // aparecem na mesma página (todos os SVGs usariam o mesmo id="grad-like"
    // e o browser aplicaria só à primeira, deixando as outras brancas).
    $uid = 'r-' . $type . '-' . bin2hex(random_bytes(4));
@endphp

@switch($type)
    {{-- LIKE — polegar pra cima azul (estilo Facebook) --}}
    @case('like')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="{{ $vb }}" class="{{ $sizeClass }} {{ $animated ? 'transition-transform hover:scale-125' : '' }} shrink-0 overflow-visible" {{ $attributes }}>
            <defs>
                <linearGradient id="{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#3B82F6"/>
                    <stop offset="100%" stop-color="#1D4ED8"/>
                </linearGradient>
            </defs>
            <circle cx="12" cy="12" r="12" fill="url(#{{ $uid }})"/>
            <path d="M9 11.5v6a1 1 0 0 0 1 1h6.5c.5 0 .95-.35 1.05-.85l1.1-4.5c.15-.6-.3-1.15-.9-1.15h-3.9l.5-2.5c.2-.9-.5-1.7-1.4-1.5-.4.1-.7.4-.85.8L10.5 11h-.5a1 1 0 0 0-1 .5z" fill="white"/>
            <rect x="5.5" y="11.5" width="2.5" height="7" rx="0.5" fill="white"/>
        </svg>
        @break

    {{-- LOVE — coração vermelho gradiente --}}
    @case('love')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="{{ $vb }}" class="{{ $sizeClass }} {{ $animated ? 'transition-transform hover:scale-125' : '' }} shrink-0 overflow-visible" {{ $attributes }}>
            <defs>
                <linearGradient id="{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#F43F5E"/>
                    <stop offset="100%" stop-color="#BE123C"/>
                </linearGradient>
            </defs>
            <circle cx="12" cy="12" r="12" fill="url(#{{ $uid }})"/>
            <path d="M12 17c-.4-.3-4-2.9-4-5.8 0-1.4 1.15-2.55 2.55-2.55.8 0 1.55.4 2.05 1.05.5-.65 1.25-1.05 2.05-1.05 1.4 0 2.55 1.15 2.55 2.55 0 2.9-3.6 5.5-4 5.8z"
                  fill="white"/>
        </svg>
        @break

    {{-- CELEBRATE — confete/festa dourado --}}
    @case('celebrate')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="{{ $vb }}" class="{{ $sizeClass }} {{ $animated ? 'transition-transform hover:scale-125' : '' }} shrink-0 overflow-visible" {{ $attributes }}>
            <defs>
                <linearGradient id="{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#FBBF24"/>
                    <stop offset="100%" stop-color="#D97706"/>
                </linearGradient>
            </defs>
            <circle cx="12" cy="12" r="12" fill="url(#{{ $uid }})"/>
            {{-- Cone de festa (recentrado) --}}
            <path d="M7 17.5l3-8 5.5 5.5-8.5 2.5z" fill="white"/>
            <path d="M10 9.5l5.5 5.5" stroke="#D97706" stroke-width="0.6" stroke-linecap="round"/>
            {{-- Confetes contidos dentro do círculo --}}
            <circle cx="16" cy="7.5" r="0.7" fill="#FDE68A"/>
            <circle cx="14.5" cy="6" r="0.5" fill="#FCA5A5"/>
            <circle cx="17" cy="10" r="0.6" fill="#6EE7B7"/>
            <circle cx="13.5" cy="8.5" r="0.4" fill="white"/>
            <circle cx="17.5" cy="13" r="0.5" fill="#93C5FD"/>
        </svg>
        @break

    {{-- SUPPORT — coração com mãos (símbolo de apoio) laranja --}}
    @case('support')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="{{ $vb }}" class="{{ $sizeClass }} {{ $animated ? 'transition-transform hover:scale-125' : '' }} shrink-0 overflow-visible" {{ $attributes }}>
            <defs>
                <linearGradient id="{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#FB923C"/>
                    <stop offset="100%" stop-color="#EA580C"/>
                </linearGradient>
            </defs>
            <circle cx="12" cy="12" r="12" fill="url(#{{ $uid }})"/>
            {{-- Coração menor bem centralizado --}}
            <path d="M12 16.5c-.35-.25-3.5-2.55-3.5-5.05 0-1.2 1-2.2 2.2-2.2.7 0 1.35.35 1.75.9.4-.55 1.05-.9 1.75-.9 1.2 0 2.2 1 2.2 2.2 0 2.5-3.15 4.8-3.5 5.05z"
                  fill="white"/>
            {{-- Mãos apoiando por baixo (arcos) --}}
            <path d="M7.5 15.5c1.5 1.5 3 2 4.5 2s3-.5 4.5-2" stroke="white" stroke-width="1" stroke-linecap="round" fill="none"/>
        </svg>
        @break

    {{-- INSIGHTFUL — lâmpada roxa --}}
    @case('insightful')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="{{ $vb }}" class="{{ $sizeClass }} {{ $animated ? 'transition-transform hover:scale-125' : '' }} shrink-0 overflow-visible" {{ $attributes }}>
            <defs>
                <linearGradient id="{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#A78BFA"/>
                    <stop offset="100%" stop-color="#7C3AED"/>
                </linearGradient>
            </defs>
            <circle cx="12" cy="12" r="12" fill="url(#{{ $uid }})"/>
            {{-- Bulbo --}}
            <path d="M9.5 11c0-1.4 1.1-2.5 2.5-2.5s2.5 1.1 2.5 2.5c0 1-.55 1.85-1.3 2.3v1h-2.4v-1c-.75-.45-1.3-1.3-1.3-2.3z"
                  fill="#FEF3C7"/>
            {{-- Base da lâmpada --}}
            <rect x="10.7" y="14.5" width="2.6" height="1" rx="0.3" fill="white"/>
            <rect x="10.9" y="15.5" width="2.2" height="0.8" rx="0.3" fill="white"/>
            {{-- Raios curtos ao redor --}}
            <path d="M12 6v1.3M7.2 11h1.3M15.5 11h1.3M8.5 7.5l.9.9M15.5 7.5l-.9.9"
                  stroke="white" stroke-width="0.8" stroke-linecap="round"/>
        </svg>
        @break

    {{-- FUNNY — rosto rindo amarelo --}}
    @case('funny')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="{{ $vb }}" class="{{ $sizeClass }} {{ $animated ? 'transition-transform hover:scale-125' : '' }} shrink-0 overflow-visible" {{ $attributes }}>
            <defs>
                <linearGradient id="{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#FDE047"/>
                    <stop offset="100%" stop-color="#CA8A04"/>
                </linearGradient>
            </defs>
            <circle cx="12" cy="12" r="12" fill="url(#{{ $uid }})"/>
            {{-- Olhos fechados (traços em U) contidos dentro --}}
            <path d="M8 10.5c.4-.8 1.2-1.2 2-.8s1.2 1.2.8 2" stroke="#1f2937" stroke-width="1.1" stroke-linecap="round" fill="none"/>
            <path d="M13.2 11.7c-.4-.8.4-1.6 1.2-2s1.6 0 2 .8" stroke="#1f2937" stroke-width="1.1" stroke-linecap="round" fill="none"/>
            {{-- Boca aberta rindo --}}
            <path d="M7.5 14c.8 2.5 2.5 3.5 4.5 3.5s3.7-1 4.5-3.5z" fill="#1f2937"/>
            {{-- Língua --}}
            <path d="M9.5 15.7c.8.9 4.2.9 5 0 0-.2-.1-.4-.3-.5-1.3.4-3.1.4-4.4 0-.2.1-.3.3-.3.5z" fill="#F43F5E"/>
        </svg>
        @break

    @default
        {{-- fallback: mostra o like --}}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="{{ $vb }}" class="{{ $sizeClass }} shrink-0 overflow-visible" {{ $attributes }}>
            <circle cx="12" cy="12" r="12" fill="#3B82F6"/>
            <path d="M9 11.5v6a1 1 0 0 0 1 1h6.5c.5 0 .95-.35 1.05-.85l1.1-4.5c.15-.6-.3-1.15-.9-1.15h-3.9l.5-2.5c.2-.9-.5-1.7-1.4-1.5-.4.1-.7.4-.85.8L10.5 11h-.5a1 1 0 0 0-1 .5z" fill="white"/>
            <rect x="5.5" y="11.5" width="2.5" height="7" rx="0.5" fill="white"/>
        </svg>
@endswitch
