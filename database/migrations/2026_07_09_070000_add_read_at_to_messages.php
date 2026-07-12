<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona `read_at` em messages para status de leitura estilo WhatsApp.
 * - null       → apenas enviado
 * - preenchido → lido pelo destinatário
 * (Entrega assíncrona por broadcast é feature futura; por ora tratamos
 *  "entregue" como "enviado" já que a persistência é síncrona.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('attachments');
                $table->index(['conversation_id', 'read_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'read_at')) {
                $table->dropIndex(['conversation_id', 'read_at']);
                $table->dropColumn('read_at');
            }
        });
    }
};
