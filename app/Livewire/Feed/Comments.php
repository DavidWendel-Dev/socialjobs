<?php

declare(strict_types=1);

namespace App\Livewire\Feed;

use App\Models\Comment;
use App\Models\Post;
use App\Services\MentionService;
use App\Services\PointsService;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Comments extends Component
{
    public int $postId = 0;

    #[Validate('required|string|min:1|max:2000')]
    public string $body = '';

    /**
     * ID do comentário pai quando o usuário está respondendo alguém.
     * null = comentário raiz no post.
     */
    public ?int $replyingTo = null;

    /** Nome do usuário para quem estamos respondendo (só para exibir na UI). */
    public string $replyingToName = '';

    /* ============================================================
     |  Menções (autocomplete)
     |============================================================ */

    /** Query atual do autocomplete (sem o @). */
    public string $mentionQuery = '';

    /** Resultados atuais do autocomplete. */
    public array $mentionResults = [];

    /**
     * Recalcula sugestões quando `mentionQuery` muda (via wire:model.live).
     * Query vazia = mostra os users mais populares (para quem apenas digitou "@").
     */
    public function updatedMentionQuery(): void
    {
        $this->mentionResults = app(MentionService::class)
            ->suggest($this->mentionQuery, 6, auth()->id());
    }

    /**
     * Fecha o autocomplete.
     */
    public function closeMentions(): void
    {
        $this->mentionQuery   = '';
        $this->mentionResults = [];
    }

    /* ============================================================
     |  Respostas
     |============================================================ */

    /**
     * Marca um comentário como "sendo respondido" — pré-preenche @username.
     */
    public function setReply(int $commentId): void
    {
        $c = Comment::with('user:id,name,username')->find($commentId);
        if (! $c) {
            return;
        }
        $this->replyingTo     = $c->id;
        $this->replyingToName = $c->user?->name ?? 'usuário';

        // Pré-insere a menção
        $username = $c->user?->username;
        if ($username && ! str_contains($this->body, '@' . $username)) {
            $this->body = '@' . $username . ' ' . $this->body;
        }
    }

    public function cancelReply(): void
    {
        $this->replyingTo     = null;
        $this->replyingToName = '';
    }

    /* ============================================================
     |  CRUD
     |============================================================ */

    public function submit(): void
    {
        if (! auth()->check() || ! $this->postId) {
            return;
        }
        $this->validate();

        $post = Post::find($this->postId);
        if (! $post) {
            return;
        }

        // Se replyingTo é dado, garanta que pertence a este post
        $parentId = null;
        if ($this->replyingTo) {
            $parent = Comment::find($this->replyingTo);
            if ($parent && $parent->post_id === $post->id) {
                $parentId = $parent->id;
            }
        }

        $comment = Comment::create([
            'user_id'   => auth()->id(),
            'post_id'   => $post->id,
            'parent_id' => $parentId,
            'body'      => trim($this->body),
        ]);

        // Notifica o dono do post (se não for o próprio autor do comentário)
        if ($post->user && $post->user_id !== auth()->id()) {
            $post->user->notify(new \App\Notifications\NewCommentNotification($comment, $post));
        }

        // Menções
        app(MentionService::class)->syncMentions($comment, $comment->body, auth()->user());

        // XP
        $svc = app(PointsService::class);
        $svc->award(
            auth()->user(),
            'comment.created',
            $comment,
            'comment.created:' . $comment->id
        );

        // XP para o dono do post (só uma vez por comentário)
        if ($post->user_id !== auth()->id()) {
            $owner = $post->user;
            if ($owner) {
                $svc->award(
                    $owner,
                    'reaction.received',
                    $comment,
                    'comment.received:' . $comment->id
                );
            }
        }

        $this->reset(['body', 'replyingTo', 'replyingToName', 'mentionQuery', 'mentionResults']);
        $this->dispatch('comment-added', postId: $this->postId);
    }

    public function remove(int $commentId): void
    {
        if (! auth()->check()) {
            return;
        }
        $comment = Comment::find($commentId);
        if (! $comment || $comment->user_id !== auth()->id()) {
            return;
        }
        // Ao deletar um comentário, respostas ficam órfãs — vou promover para raiz
        Comment::where('parent_id', $comment->id)->update(['parent_id' => null]);
        $comment->delete();

        $this->dispatch('comment-removed', postId: $this->postId);
    }

    public function render()
    {
        // Carrega TODOS os comentários do post (com autor) e depois montamos árvore em memória.
        $all = Comment::query()
            ->where('post_id', $this->postId)
            ->with('user')
            ->oldest()
            ->limit(500)
            ->get();

        // Agrupa por parent_id
        $byParent = $all->groupBy(fn ($c) => $c->parent_id);
        $roots    = $byParent->get(null, collect())->values();

        // Anexa recursivamente uma "children" para cada comentário raiz.
        $attachChildren = function ($comments) use (&$attachChildren, $byParent) {
            foreach ($comments as $c) {
                $children = $byParent->get($c->id, collect())->values();
                $attachChildren($children);
                $c->setRelation('replies', $children);
            }
        };
        $attachChildren($roots);

        return view('livewire.feed.comments', [
            'roots' => $roots,
            'total' => $all->count(),
        ]);
    }
}
