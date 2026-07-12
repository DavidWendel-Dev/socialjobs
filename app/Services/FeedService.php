<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\CursorPaginator;

/**
 * Constrói o feed principal com 3 filtros: foryou, following, global.
 */
class FeedService
{
    /**
     * @param 'foryou'|'following'|'global' $filter
     */
    public function timeline(User $user, string $filter = 'foryou', ?int $cursor = null): CursorPaginator
    {
        $query = Post::query()
            ->with([
                'user:id,name,username,avatar_path,headline',
                'media',
                'comments' => fn ($q) => $q->latest()->limit(2)->with('user:id,name,username,avatar_path'),
            ])
            ->withCount(['reactions', 'comments']);

        switch ($filter) {
            case 'following':
                // Posts das pessoas que sigo + meus próprios
                $followingIds = $user->follows()->pluck('users.id')->push($user->id)->all();
                $query->whereIn('user_id', $followingIds)
                    ->whereIn('visibility', ['public', 'followers']);
                break;

            case 'global':
                $query->where('visibility', 'public');
                break;

            case 'foryou':
            default:
                // Mistura: posts públicos + seguindo, ordenados por engajamento recente
                $query->where(function ($q) use ($user) {
                    $followingIds = $user->follows()->pluck('users.id')->all();
                    $q->where('visibility', 'public')
                        ->orWhere(function ($sub) use ($followingIds) {
                            $sub->where('visibility', 'followers')->whereIn('user_id', $followingIds);
                        });
                });
                break;
        }

        return $query->latest()->cursorPaginate(15);
    }
}
