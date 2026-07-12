<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait para tornar um Model "reagível" (posts, comments, etc.).
 * Fornece contagem agrupada por tipo e método de consulta da reação do usuário atual.
 */
trait HasReactions
{
    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    /**
     * Retorna um array associativo [tipo => quantidade].
     * Tipos sem reações não aparecem no resultado.
     *
     * @return array<string,int>
     */
    public function reactionCounts(): array
    {
        return $this->reactions()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * Retorna o tipo da reação do usuário informado no target atual,
     * ou null se ele nunca reagiu.
     */
    public function reactedBy(User $user): ?string
    {
        $reaction = $this->reactions()->where('user_id', $user->id)->first();

        return $reaction?->type;
    }
}
