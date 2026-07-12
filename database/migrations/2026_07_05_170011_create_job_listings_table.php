<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('description');
            $table->enum('seniority', ['junior', 'mid', 'senior', 'lead'])->default('mid');
            $table->enum('modality', ['remote', 'hybrid', 'onsite'])->default('remote');
            $table->enum('contract_type', ['clt', 'pj', 'freelance', 'internship'])->default('clt');
            $table->string('location')->nullable();
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->enum('status', ['draft', 'open', 'paused', 'closed'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index('modality');
            $table->index('seniority');
        });

        // Fulltext em (title, description) — só MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE job_listings ADD FULLTEXT job_listings_fulltext (title, description)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
