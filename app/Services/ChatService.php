<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Chat 1:1 e (futuro) grupo — broadcast é feito via Reverb pelos eventos que
 * consumam este service. Aqui garantimos apenas persistência atômica.
 */
class ChatService
{
    /**
     * Retorna uma conversa DM entre dois usuários — cria se não existir.
     */
    public function findOrCreateDm(User $a, User $b): Conversation
    {
        // Busca conversa DM que contenha os dois usuários
        $conversation = Conversation::query()
            ->where('type', 'dm')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $a->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $b->id))
            ->first();

        if ($conversation) {
            return $conversation;
        }

        return DB::transaction(function () use ($a, $b) {
            $conversation = Conversation::create(['type' => 'dm']);
            ConversationParticipant::insert([
                ['conversation_id' => $conversation->id, 'user_id' => $a->id, 'last_read_at' => null],
                ['conversation_id' => $conversation->id, 'user_id' => $b->id, 'last_read_at' => null],
            ]);

            return $conversation;
        });
    }

    /**
     * Envia uma mensagem e dispara broadcast (via event MessageSent — a ser criado).
     * Lança BlockedException se envio bloqueado.
     *
     * @param array<int,array<string,mixed>> $attachments
     * @throws \RuntimeException se qualquer participante bloqueou o remetente ou vice-versa
     */
    public function send(Conversation $c, User $sender, string $body, array $attachments = []): Message
    {
        // Bloqueio: se sender bloqueou algum participante OU alguém bloqueou sender, veta.
        $c->loadMissing('participants');
        foreach ($c->participants as $participant) {
            if ((int) $participant->id === (int) $sender->id) continue;
            if ($sender->hasBlocked($participant) || $participant->hasBlocked($sender)) {
                throw new \RuntimeException('blocked');
            }
        }

        $message = Message::create([
            'conversation_id' => $c->id,
            'user_id'         => $sender->id,
            'body'            => $body,
            'attachments'     => $attachments,
        ]);

        // Notifica os outros participantes da conversa (respeita mute)
        foreach ($c->participants as $participant) {
            if ((int) $participant->id === (int) $sender->id) continue;
            if ($participant->hasMuted($sender)) continue; // silenciou → não notifica
            $participant->notify(new \App\Notifications\NewMessageNotification($message, $c));
        }

        return $message;
    }

    /**
     * Marca todas as mensagens não lidas da conversa como lidas pelo $reader
     * (i.e., mensagens enviadas por outros participantes que ainda não tinham read_at).
     * Retorna quantas foram marcadas.
     */
    public function markAsRead(Conversation $c, User $reader): int
    {
        return (int) Message::where('conversation_id', $c->id)
            ->where('user_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
