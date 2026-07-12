<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Você recebeu uma nova mensagem em uma conversa.
 */
class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Message $message,
        public Conversation $conversation
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $sender = $this->message->user;

        return [
            'type'            => 'new_message',
            'actor_id'        => $sender?->id,
            'actor_name'      => $sender?->name ?? 'Alguém',
            'actor_avatar'    => $sender?->avatar_url ?? null,
            'conversation_id' => $this->conversation->id,
            'excerpt'         => \Illuminate\Support\Str::limit((string) $this->message->body, 90),
            'url'             => route('messages.index', ['conversationId' => $this->conversation->id]),
            'message'         => ($sender?->name ?? 'Alguém') . ' te mandou uma mensagem',
            'icon'            => 'message',
        ];
    }
}
