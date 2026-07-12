<?php

declare(strict_types=1);

namespace App\Livewire\Feed;

use App\Models\Post;
use App\Services\ReactionService;
use App\Services\ViewTrackerService;
use Livewire\Component;

class PostCard extends Component
{
    /** ID do post que este card exibe. */
    public int $postId = 0;

    public function mount(?Post $post = null, ?int $postId = null): void
    {
        if ($post && $post->exists) {
            $this->postId = $post->id;
        } elseif ($postId) {
            $this->postId = $postId;
        }

        // Registra view (1x por sessão em janela de 6h; ignora autor)
        if ($this->postId > 0) {
            $authorId = $post?->user_id
                ?? Post::where('id', $this->postId)->value('user_id');
            app(ViewTrackerService::class)->trackPost($this->postId, (int) $authorId);
        }
    }

    /**
     * Aplica/troca/remove a reação do usuário no post.
     */
    public function react(string $type): void
    {
        if (! auth()->check() || ! $this->postId) {
            return;
        }

        $post = Post::find($this->postId);
        if (! $post) {
            return;
        }

        $svc  = app(ReactionService::class);
        $user = auth()->user();

        $current = $post->reactions()
            ->where('user_id', $user->id)
            ->value('type');

        if ($current === $type) {
            $svc->unreact($user, $post);
        } else {
            $svc->react($user, $post, $type);
        }

        $this->dispatch('post-reacted', postId: $this->postId, type: $type);
    }

    /**
     * Exclui o post — apenas o autor pode.
     * Emite evento post-deleted para o Timeline recarregar.
     */
    public function delete(): void
    {
        if (! auth()->check() || ! $this->postId) {
            return;
        }

        $post = Post::find($this->postId);
        if (! $post) {
            return;
        }

        // Autorização: apenas o dono do post
        if ($post->user_id !== auth()->id()) {
            return;
        }

        $post->delete();

        // Zera para a view mostrar "Post indisponível" no próximo render
        $this->postId = 0;

        $this->dispatch('post-deleted', postId: $post->id);
    }

    public function render()
    {
        $post = Post::with(['user.companyProfile', 'media'])->find($this->postId);

        $counts        = [];
        $totalCount    = 0;
        $myReaction    = null;
        $commentsCount = 0;
        $isOwner       = false;

        if ($post) {
            $svc         = app(ReactionService::class);
            $counts      = $svc->counts($post);
            $totalCount  = array_sum($counts);
            $commentsCount = $post->comments()->count();
            $isOwner     = auth()->check() && auth()->id() === $post->user_id;

            if (auth()->check()) {
                $myReaction = $post->reactions()
                    ->where('user_id', auth()->id())
                    ->value('type');
            }
        }

        return view('livewire.feed.post-card', [
            'post'          => $post,
            'reactionTypes' => config('reactions.types', []),
            'counts'        => $counts,
            'totalCount'    => $totalCount,
            'myReaction'    => $myReaction,
            'commentsCount' => $commentsCount,
            'isOwner'       => $isOwner,
        ]);
    }
}
