@props([
    'padded' => true,
    'as' => 'div',
])

<{{ $as }} {{ $attributes->merge(['class' => 'rounded-2xl bg-white shadow-soft ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-800 ' . ($padded ? 'p-6' : '')]) }}>
    {{ $slot }}
</{{ $as }}>
