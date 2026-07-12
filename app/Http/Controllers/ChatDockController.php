<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints JSON usados pelo widget flutuante de chat (canto inferior direito).
 * Nada de Livewire: apenas fetch() vindo do Alpine.js.
 */
class ChatDockController extends Controller
{
    /** Lista as últimas 20 conversas do usuário. */
    public function conversations(): JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['conversations' => []], 401);
        }

        $items = Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->with([
                'participants:id,name,type,avatar_path,username',
                'participants.companyProfile:id,user_id,trade_name,legal_name,slug',
                'messages' => fn ($q) => $q->latest()->limit(1),
            ])
            ->latest('updated_at')
            ->limit(20)
            ->get()
            ->filter(function (Conversation $c) use ($user) {
                // Esconde conversas com quem bloqueou ou foi bloqueado
                $other = $c->participants->first(fn (User $u) => $u->id !== $user->id);
                if (! $other) return true;
                return ! $user->hasBlocked($other) && ! $user->isBlockedBy($other);
            })
            ->values()
            ->map(function (Conversation $c) use ($user) {
                $other = $c->participants->first(fn (User $u) => $u->id !== $user->id);
                $last = $c->messages->first();
                return [
                    'id'           => $c->id,
                    'other_id'     => $other?->id,
                    'other_name'   => $other?->display_name ?? $other?->name ?? 'Usuário',
                    'other_avatar' => $other?->avatar_url,
                    'last_message' => $last?->body ? \Illuminate\Support\Str::limit((string) $last->body, 45) : 'Iniciar conversa…',
                    'updated_at'   => optional($c->updated_at)->diffForHumans(null, true),
                ];
            });

        return response()->json(['conversations' => $items]);
    }

    /** Retorna as últimas 50 mensagens de uma conversa. */
    public function messages(int $conversationId): JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['messages' => []], 401);
        }

        $conversation = Conversation::query()
            ->where('id', $conversationId)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->with([
                'participants:id,name,type,avatar_path,username',
                'participants.companyProfile:id,user_id,trade_name,legal_name,slug',
            ])
            ->first();

        if (! $conversation) {
            return response()->json(['error' => 'not_found'], 404);
        }

        $other = $conversation->participants->first(fn (User $u) => $u->id !== $user->id);

        // Marca msgs recebidas como lidas ao abrir a thread
        app(ChatService::class)->markAsRead($conversation, $user);

        // Status de bloqueio nas duas direções
        $isBlocked = $other ? $user->hasBlocked($other) : false;
        $blockedBy = $other ? $user->isBlockedBy($other) : false;

        $messages = Message::query()
            ->where('conversation_id', $conversationId)
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (Message $m) => [
                'id'         => $m->id,
                'mine'       => $m->user_id === $user->id,
                'body'       => $m->body,
                'created_at' => optional($m->created_at)->format('H:i'),
                'read'       => $m->read_at !== null,
            ]);

        return response()->json([
            'other' => [
                'id'          => $other?->id,
                'name'        => $other?->display_name ?? $other?->name ?? 'Usuário',
                'avatar'      => $other?->avatar_url,
                'username'    => $other?->username,
                'profile_url' => $this->profileUrlFor($other),
            ],
            'messages'   => $messages,
            'is_blocked' => $isBlocked,
            'blocked_by' => $blockedBy,
        ]);
    }

    /** Retorna a URL correta do perfil (empresa /c/{slug} ou candidato /u/{username}). */
    private function profileUrlFor(?User $u): ?string
    {
        if (! $u) return null;
        if (($u->type ?? 'candidate') === 'company') {
            $slug = optional($u->companyProfile)->slug;
            if ($slug) return url('/c/' . $slug);
        }
        return url('/u/' . ($u->username ?? $u->id));
    }

    /** Envia mensagem em uma conversa. */
    public function send(Request $request, int $conversationId): JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $conversation = Conversation::query()
            ->where('id', $conversationId)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->first();

        if (! $conversation) {
            return response()->json(['error' => 'not_found'], 404);
        }

        try {
            $msg = app(ChatService::class)->send($conversation, $user, $data['body']);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'blocked') {
                return response()->json(['error' => 'blocked'], 403);
            }
            throw $e;
        }
        $conversation->touch();

        return response()->json([
            'ok'      => true,
            'message' => [
                'id'         => $msg->id,
                'mine'       => true,
                'body'       => $msg->body,
                'created_at' => optional($msg->created_at)->format('H:i'),
                'read'       => false,
            ],
        ]);
    }

    /** Inicia (ou reutiliza) uma DM com outro usuário. */
    public function startDm(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $data = $request->validate([
            'user_id' => ['required', 'integer'],
        ]);
        if ((int) $data['user_id'] === $user->id) {
            return response()->json(['error' => 'self'], 422);
        }

        $other = User::find($data['user_id']);
        if (! $other) {
            return response()->json(['error' => 'not_found'], 404);
        }

        $conv = app(ChatService::class)->findOrCreateDm($user, $other);

        return response()->json([
            'ok'              => true,
            'conversation_id' => $conv->id,
        ]);
    }
}
