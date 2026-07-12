<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Muda `messages.attachments` de JSON pra TEXT.
 * Motivo: o cast `encrypted:array` no model gera uma string opaca (Laravel
 * encrypter), não um JSON válido. Enquanto a coluna estava como JSON, o MySQL
 * rejeitava com "3140 Invalid JSON text" ao inserir qualquer mensagem.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->text('attachments')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->json('attachments')->nullable()->change();
        });
    }
};
