<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_stats', function (Blueprint $table) {
            // PK é user_id direto (1:1 com users)
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->integer('total_xp')->default(0);
            $table->tinyInteger('level')->default(1);
            $table->integer('login_streak')->default(0);
            $table->date('last_login_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('total_xp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_stats');
    }
};
