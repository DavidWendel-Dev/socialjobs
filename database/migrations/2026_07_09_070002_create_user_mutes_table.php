<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Silenciamento (mute): muter_id silenciou muted_id.
 * Não impede envio nem visualização — apenas suprime notificações.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_mutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('muted_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['muter_id', 'muted_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_mutes');
    }
};
