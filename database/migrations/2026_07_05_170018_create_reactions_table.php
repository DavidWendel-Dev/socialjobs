<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reactable_type', 60);
            $table->unsignedBigInteger('reactable_id');
            $table->enum('type', ['like', 'love', 'celebrate', 'support', 'insightful', 'funny'])->default('like');
            $table->timestamps();

            $table->unique(['user_id', 'reactable_type', 'reactable_id'], 'reactions_user_target_unique');
            $table->index(['reactable_type', 'reactable_id', 'type'], 'reactions_target_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
