<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['received', 'reviewing', 'interview', 'offer', 'hired', 'rejected'])->default('received');
            $table->text('cover_letter')->nullable();
            $table->decimal('match_score', 5, 2)->nullable();
            $table->text('internal_note')->nullable();
            $table->timestamps();

            $table->unique(['job_listing_id', 'user_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
