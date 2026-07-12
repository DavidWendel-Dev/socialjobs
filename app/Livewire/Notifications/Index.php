<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Página completa de notificações (/notifications).
 * Paginação, filtro por status (lidas/não lidas), ações em massa.
 */
#[Layout('layouts.app')]
#[Title('Notificações · SocialJobs')]
class Index extends Component
{
    use WithPagination;

    /** @var 'all'|'unread' */
    public string $filter = 'all';

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['all', 'unread'], true) ? $filter : 'all';
        $this->resetPage();
    }

    public function markAsRead(string $id): void
    {
        $n = auth()->user()?->notifications()->where('id', $id)->first();
        if ($n && ! $n->read_at) {
            $n->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        auth()->user()?->unreadNotifications->markAsRead();
    }

    public function render(): View
    {
        $user = auth()->user();
        $query = $user
            ? ($this->filter === 'unread'
                ? $user->unreadNotifications()
                : $user->notifications())
            : null;

        $notifications = $query
            ? $query->latest()->paginate(20)
            : collect();

        return view('livewire.notifications.index', [
            'notifications' => $notifications,
            'unreadCount'   => (int) ($user?->unreadNotifications()->count() ?? 0),
        ]);
    }
}
