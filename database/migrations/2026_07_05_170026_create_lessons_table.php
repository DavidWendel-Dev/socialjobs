<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->enum('video_provider', ['youtube', 'vimeo', 'upload'])->default('youtube');
            $table->string('video_ref')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->longText('transcript')->nullable();
            $table->longText('content_markdown')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->unique(['module_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
