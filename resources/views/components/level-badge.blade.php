@props([
    'user' => null,
    'showName' => true,
    'size' => 'md',
])

@php
    // Estado padrão (fallback caso não haja usuário/stats)
    $level = ['level' => 1, 'name' => 'Explorador', 'ring_color' => '#94A3B8'];

    if ($user) {
        try {
            // Total de XP do usuário (0 se stats não existir)
            $totalXp = (int) ($user->stats?->total_xp ?? 0);

            // computeLevel espera INT
            if (class_exists(\App\Services\LevelService::class)) {
                $computed = app(\App\Services\LevelService::class)->computeLevel($totalXp);
                if (is_array($computed)) {
                    $level = array_merge($level, $computed);
                }
            }
        } catch (\Throwable $e) {
            // usa o default
        }
    }

    $sizes = [
        'sm' => 'text-[10px] px-2 py-0.5',
        'md' => 'text-xs px-2.5 py-1',
        'lg' => 'text-sm px-3 py-1.5',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

{{-- whitespace-nowrap + shrink-0 evita que o badge quebre de linha quando o nome
     do usuário for grande e force wrap dentro de um flex. --}}
<span {{ $attributes->merge([
        'class' => 'inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full font-semibold text-white ' . $sizeClass
    ]) }}
      style="background-color: {{ $level['ring_color'] ?? '#94A3B8' }};">
    <span class="inline-block h-1.5 w-1.5 rounded-full bg-white/80"></span>
    <span>Nv. {{ $level['level'] ?? 1 }}</span>
    @if ($showName)
        <span class="opacity-90">· {{ $level['name'] ?? 'Explorador' }}</span>
    @endif
</span>
