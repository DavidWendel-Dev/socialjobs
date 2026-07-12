<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Wrapper simples sobre o sistema de notificações do Laravel.
 * Cria registro na tabela `notifications` e (quando configurado no evento)
 * faz broadcast automático via Reverb.
 */
class NotificationService
{
    /**
     * Cria uma notificação no banco para o usuário.
     *
     * @param array<string,mixed> $data
     */
    public function notify(User $user, string $type, array $data): DatabaseNotification
    {
        $notification = new DatabaseNotification([
            'id'              => (string) Str::uuid(),
            'type'            => $type,
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id'   => $user->id,
            'data'            => $data,
            'read_at'         => null,
        ]);
        $notification->save();

        return $notification;
    }

    /**
     * Envia uma notificação Laravel (classe Notification) para múltiplos usuários.
     *
     * @param iterable<User> $users
     */
    public function broadcast(iterable $users, \Illuminate\Notifications\Notification $notification): void
    {
        Notification::send($users, $notification);
    }
}
