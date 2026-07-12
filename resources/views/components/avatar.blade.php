@props([
    'user' => null,
    'size' => 'md',
    'ring' => null,
])

@php
    $sizes = [
        'xs' => 'w-6 h-6 text-[10px]',
        'sm' => 'w-8 h-8 text-xs',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-16 h-16 text-lg',
        'xl' => 'w-32 h-32 text-3xl',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];

    $name = is_object($user) ? ($user->name ?? 'Usuário') : (is_string($user) ? $user : 'Usuário');
    $initials = collect(explode(' ', trim((string) $name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    if ($initials === '') { $initials = 'U'; }

    $avatarUrl = is_object($user) ? ($user->avatar_url ?? $user->profile_photo_url ?? null) : null;

    // Cores determinísticas a partir do nome
    $palette = ['bg-brand-500', 'bg-accent-500', 'bg-sky-500', 'bg-violet-500', 'bg-rose-500', 'bg-amber-500'];
    $bg = $palette[abs(crc32($name)) % count($palette)];
@endphp

<span {{ $attributes->merge(['class' => 'relative inline-flex items-center justify-center overflow-hidden rounded-full font-semibold text-white ' . $sizeClass . ' ' . $bg . ($ring ? ' ring-2 ring-offset-2 ' . $ring : '')]) }}>
    @if($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="{{ $name }}" class="w-full h-full object-cover">
    @else
        <span>{{ $initials }}</span>
    @endif
</span>
