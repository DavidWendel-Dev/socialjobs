<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Alguém começou a te seguir.
 */
class NewFollowerNotification extends Notification
{
    use Queueable;

    public function __construct(public User $follower) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $username = $this->follower->username ?? $this->follower->id;

        return [
            'type'         => 'new_follower',
            'actor_id'     => $this->follower->id,
            'actor_name'   => $this->follower->name,
            'actor_avatar' => $this->follower->avatar_url ?? null,
            'url'          => url('/u/' . $username),
            'message'      => $this->follower->name . ' começou a te seguir',
            'icon'         => 'user',
        ];
    }
}
