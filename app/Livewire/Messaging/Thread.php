<?php

declare(strict_types=1);

namespace App\Livewire\Messaging;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserMute;
use App\Services\ChatService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Conversa · SocialJobs')]
class Thread extends Component
{
    /** ID da conversa aberta. */
    public ?int $conversationId = null;

    /** Corpo da nova mensagem sendo digitada. */
    public string $body = '';

    /** Feedback ao usuário sobre operações (bloqueio, silenciar, etc.) */
    public ?string $flash = null;

    public function mount(?Conversation $conversation = null, ?int $conversationId = null): void
    {
        if ($conversation && $conversation->exists) {
            $this->conversationId = $conversation->id;
        } elseif ($conversationId) {
            $this->conversationId = $conversationId;
        }

        // Marca msgs recebidas como lidas ao entrar na conversa
        $this->markCurrentAsRead();
    }

    public function send(): void
    {
        $body = trim($this->body);
        if ($body === '' || ! $this->conversationId || ! auth()->check()) {
            return;
        }

        $conversation = $this->loadConversation();
        if (! $conversation) {
            return;
        }

        $isParticipant = $conversation->participants->contains(fn ($u) => $u->id === auth()->id());
        if (! $isParticipant) {
            return;
        }

        try {
            app(ChatService::class)->send($conversation, auth()->user(), $body);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'blocked') {
                $this->flash = 'Vocês não podem trocar mensagens (bloqueio ativo).';
                return;
            }
            throw $e;
        }

        $conversation->touch();
        $this->body = '';
        $this->flash = null;
    }

    /** Bloqueia o outro participante da conversa. */
    public function block(): void
    {
        $other = $this->otherUser();
        if (! $other) return;
        UserBlock::firstOrCreate(['blocker_id' => auth()->id(), 'blocked_id' => $other->id]);
        $this->flash = $other->display_name . ' foi bloqueado.';
    }

    /** Desbloqueia o outro participante. */
    public function unblock(): void
    {
        $other = $this->otherUser();
        if (! $other) return;
        UserBlock::where('blocker_id', auth()->id())->where('blocked_id', $other->id)->delete();
        $this->flash = $other->display_name . ' foi desbloqueado.';
    }

    /** Silencia notificações do outro participante. */
    public function mute(): void
    {
        $other = $this->otherUser();
        if (! $other) return;
        UserMute::firstOrCreate(['muter_id' => auth()->id(), 'muted_id' => $other->id]);
        $this->flash = 'Notificações de ' . $other->display_name . ' silenciadas.';
    }

    /** Reativa notificações do outro participante. */
    public function unmute(): void
    {
        $other = $this->otherUser();
        if (! $other) return;
        UserMute::where('muter_id', auth()->id())->where('muted_id', $other->id)->delete();
        $this->flash = 'Notificações de ' . $other->display_name . ' reativadas.';
    }

    private function otherUser(): ?User
    {
        $conv = $this->loadConversation();
        if (! $conv) return null;
        return $conv->participants->first(fn ($u) => $u->id !== auth()->id());
    }

    private function markCurrentAsRead(): void
    {
        if (! $this->conversationId || ! auth()->check()) return;
        $conv = Conversation::find($this->conversationId);
        if (! $conv) return;
        $isPart = $conv->participants()->where('user_id', auth()->id())->exists();
        if (! $isPart) return;
        app(ChatService::class)->markAsRead($conv, auth()->user());
    }

    private function loadConversation(): ?Conversation
    {
        if (! $this->conversationId) return null;
        return Conversation::with('participants.companyProfile')->find($this->conversationId);
    }

    public function render(): View
    {
        $conversation = $this->loadConversation();

        $messages   = collect();
        $other      = null;
        $isBlocked  = false;
        $blockedBy  = false;
        $isMuted    = false;

        if ($conversation && auth()->check()) {
            $isParticipant = $conversation->participants->contains(fn ($u) => $u->id === auth()->id());

            if ($isParticipant) {
                $messages = Message::query()
                    ->where('conversation_id', $conversation->id)
                    ->with('user')
                    ->orderBy('created_at')
                    ->limit(200)
                    ->get();

                $other = $conversation->participants->first(fn ($u) => $u->id !== auth()->id());

                if ($other) {
                    $me = auth()->user();
                    $isBlocked = $me->hasBlocked($other);
                    $blockedBy = $me->isBlockedBy($other);
                    $isMuted   = $me->hasMuted($other);
                }
            }
        }

        return view('livewire.messaging.thread', [
            'conversation' => $conversation,
            'messages'     => $messages,
            'other'        => $other,
            'isBlocked'    => $isBlocked,
            'blockedBy'    => $blockedBy,
            'isMuted'      => $isMuted,
        ]);
    }
}
