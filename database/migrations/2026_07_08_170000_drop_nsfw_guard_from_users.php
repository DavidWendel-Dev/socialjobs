<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remove as colunas do sistema NSFW client-side (NSFWJS).
 * O sistema foi descontinuado — agora usamos apenas a API server-side
 * (Oanor) para moderação de imagens.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nsfw_scan_blocked_at')) {
                $table->dropColumn('nsfw_scan_blocked_at');
            }
            if (Schema::hasColumn('users', 'nsfw_scan_unlocks_at')) {
                $table->dropColumn('nsfw_scan_unlocks_at');
            }
        });
    }

    public function down(): void
    {
        // Não recriamos as colunas — o feature foi removido definitivamente.
    }
};
