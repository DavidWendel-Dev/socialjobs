<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menções (@usuario) em posts e comentários — armazenadas
     * polimorficamente para poder ligar a qualquer entidade mencionável.
     */
    public function up(): void
    {
        Schema::create('mentions', function (Blueprint $table) {
            $table->id();
            // Quem fez a menção
            $table->foreignId('mentioner_id')->constrained('users')->cascadeOnDelete();
            // Quem foi mencionado
            $table->foreignId('mentioned_id')->constrained('users')->cascadeOnDelete();
            // Contexto polimórfico: pode ser Post, Comment (ou no futuro Message)
            $table->morphs('mentionable'); // gera mentionable_type e mentionable_id
            $table->timestamps();

            // Evita a mesma menção duplicada no mesmo contexto
            $table->unique([
                'mentioned_id', 'mentionable_type', 'mentionable_id',
            ], 'mentions_unique_context');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentions');
    }
};
