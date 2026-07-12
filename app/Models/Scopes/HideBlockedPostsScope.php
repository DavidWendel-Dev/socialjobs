<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\UserBlock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope aplicado ao model Post:
 * - Se o usuário logado bloqueou alguém → esconde os posts dessa pessoa
 * - Se alguém bloqueou o usuário logado → esconde os posts dessa pessoa também
 * Assim, ninguém dos dois lados vê o conteúdo do outro no feed / perfil / etc.
 */
class HideBlockedPostsScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        // Coleção de user_ids envolvidos em bloqueio (nas duas direções)
        $blockedIds = UserBlock::query()
            ->where(function ($q) use ($user) {
                $q->where('blocker_id', $user->id) // eu bloqueei
                  ->orWhere('blocked_id', $user->id); // fui bloqueado
            })
            ->get(['blocker_id', 'blocked_id'])
            ->flatMap(fn ($row) => [$row->blocker_id, $row->blocked_id])
            ->unique()
            ->reject(fn ($id) => (int) $id === (int) $user->id)
            ->values()
            ->all();

        if (! empty($blockedIds)) {
            $builder->whereNotIn($model->getTable() . '.user_id', $blockedIds);
        }
    }
}
