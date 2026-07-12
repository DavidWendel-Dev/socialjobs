<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Alguém comentou no seu post.
 */
class NewCommentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Comment $comment,
        public Post $post
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $author = $this->comment->user;

        return [
            'type'         => 'new_comment',
            'actor_id'     => $author?->id,
            'actor_name'   => $author?->name ?? 'Alguém',
            'actor_avatar' => $author?->avatar_url ?? null,
            'post_id'      => $this->post->id,
            'comment_id'   => $this->comment->id,
            'excerpt'      => \Illuminate\Support\Str::limit(strip_tags((string) $this->comment->body), 90),
            'url'          => route('posts.show', $this->post),
            'message'      => ($author?->name ?? 'Alguém') . ' comentou no seu post',
            'icon'         => 'message',
        ];
    }
}
