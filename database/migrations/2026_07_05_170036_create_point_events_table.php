<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('point_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 60);
            // XP pode ser negativo (revogações), por isso 'integer'
            $table->integer('xp');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('dedupe_key', 120)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'dedupe_key'], 'point_events_user_dedupe_unique');
            $table->index(['user_id', 'created_at'], 'point_events_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_events');
    }
};
