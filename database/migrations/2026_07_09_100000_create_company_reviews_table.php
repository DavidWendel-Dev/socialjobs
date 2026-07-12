<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_application_id')->nullable()
                ->constrained('applications')->nullOnDelete();

            $table->tinyInteger('rating_overall');
            $table->tinyInteger('rating_process');
            $table->tinyInteger('rating_communication');
            $table->tinyInteger('rating_culture');

            $table->string('title', 191);
            $table->text('pros');
            $table->text('cons');

            $table->boolean('would_recommend')->default(true);
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_published')->default(true);

            $table->text('company_response')->nullable();
            $table->dateTime('company_responded_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'company_profile_id']);
            $table->index(['company_profile_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_reviews');
    }
};
