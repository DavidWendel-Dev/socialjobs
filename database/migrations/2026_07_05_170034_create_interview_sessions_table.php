<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('interview_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role_title');
            $table->enum('seniority', ['junior', 'mid', 'senior', 'lead'])->default('mid');
            $table->foreignId('job_listing_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('mode', ['text', 'voice'])->default('text');
            $table->enum('status', ['in_progress', 'finished', 'abandoned'])->default('in_progress');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->tinyInteger('overall_score')->nullable();
            $table->json('scores')->nullable();
            $table->longText('feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_sessions');
    }
};
