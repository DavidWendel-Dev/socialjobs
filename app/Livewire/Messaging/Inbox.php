<?php

declare(strict_types=1);

namespace App\Livewire\Messaging;

use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Mensagens · SocialJobs')]
class Inbox extends Component
{
    /** ID da conversa ativa (opcional, vem da URL). */
    #[Url]
    public ?int $conversationId = null;

    /** Busca por participante. */
    public string $search = '';

    /**
     * Se acessado com ?user=ID, inicia DM automaticamente com esse usuário.
     */
    public function mount(): void
    {
        $userId = (int) request()->query('user', 0);
        if ($userId > 0) {
            $this->startDm($userId);
        }
    }

    /**
     * Abre uma conversa no painel direito.
     */
    public function open(int $id): void
    {
        $this->conversationId = $id;
    }

    /**
     * Cria (ou reutiliza) uma DM com o usuário informado.
     * Chamado por: outros perfis, sugestões, etc.
     */
    public function startDm(int $userId): void
    {
        if (! auth()->check() || auth()->id() === $userId) {
            return;
        }

        $other = User::find($userId);
        if (! $other) {
            return;
        }

        $conversation = app(ChatService::class)->findOrCreateDm(auth()->user(), $other);
        $this->conversationId = $conversation->id;
    }

    public function render(): View
    {
        $user = auth()->user();

        // Todas as conversas em que o usuário participa
        $conversations = collect();
        if ($user) {
            $conversations = Conversation::query()
                ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
                ->with([
                    'participants', // já é belongsToMany(User)
                    // Última mensagem (para preview)
                    'messages' => fn ($q) => $q->latest()->limit(1),
                ])
                ->latest('updated_at')
                ->get()
                ->map(function (Conversation $c) use ($user) {
                    // Determina o "outro" participante (na DM)
                    $other = $c->participants
                        ->first(fn (User $u) => $u->id !== $user->id);

                    $c->setAttribute('other_user', $other);
                    $c->setAttribute('last_message', $c->messages->first());

                    return $c;
                });

            if ($this->search !== '') {
                $q = mb_strtolower($this->search);
                $conversations = $conversations->filter(function ($c) use ($q) {
                    $name = mb_strtolower((string) ($c->other_user?->name ?? ''));
                    return str_contains($name, $q);
                })->values();
            }
        }

        $active = $this->conversationId
            ? $conversations->firstWhere('id', $this->conversationId)
            : null;

        return view('livewire.messaging.inbox', [
            'conversations' => $conversations,
            'active'        => $active,
        ]);
    }
}
