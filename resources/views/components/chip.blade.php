@props([
    'color' => 'slate',
])

@php
    $palette = [
        'brand'   => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-300',
        'accent'  => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300',
        'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
        'sky'     => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300',
        'amber'   => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
        'violet'  => 'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300',
        'rose'    => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
        'slate'   => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
    ];
    $cls = $palette[$color] ?? $palette['slate'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium ' . $cls]) }}>
    {{ $slot }}
</span>
