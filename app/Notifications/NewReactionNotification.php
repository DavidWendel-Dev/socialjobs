<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Alguém reagiu ao seu post (like, love, celebrate, support, insightful, funny).
 */
class NewReactionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public User $actor,
        public Post $post,
        public string $reactionType
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $labels = [
            'like'       => 'curtiu',
            'love'       => 'amou',
            'celebrate'  => 'celebrou',
            'support'    => 'apoiou',
            'insightful' => 'achou perspicaz',
            'funny'      => 'achou engraçado',
        ];
        $verb = $labels[$this->reactionType] ?? 'reagiu a';

        return [
            'type'         => 'new_reaction',
            'actor_id'     => $this->actor->id,
            'actor_name'   => $this->actor->name,
            'actor_avatar' => $this->actor->avatar_url ?? null,
            'post_id'      => $this->post->id,
            'reaction'     => $this->reactionType,
            'url'          => route('posts.show', $this->post),
            'message'      => $this->actor->name . ' ' . $verb . ' seu post',
            'icon'         => 'heart',
        ];
    }
}
