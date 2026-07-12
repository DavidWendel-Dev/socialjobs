<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Sino de notificações no header — dropdown com últimas 10 notificações,
 * badge com contador de não lidas, botão "marcar todas como lidas".
 */
class Bell extends Component
{
    public bool $open = false;

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markAsRead(string $id): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification && ! $notification->read_at) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        auth()->user()?->unreadNotifications->markAsRead();
    }

    #[Computed]
    public function unreadCount(): int
    {
        return (int) (auth()->user()?->unreadNotifications()->count() ?? 0);
    }

    public function render(): View
    {
        $notifications = collect();
        if (auth()->check()) {
            $notifications = auth()->user()->notifications()
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('livewire.notifications.bell', [
            'notifications' => $notifications,
        ]);
    }
}
