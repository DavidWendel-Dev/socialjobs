<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sistema anti-bypass do scanner NSFW client-side.
 *
 * Se o usuário desabilitar o script NSFWJS (via AdBlock, uMatrix, etc.), o site
 * detecta e trava features de upload de imagem por 24h para "aprendizado".
 * O contador começa quando o bloqueio é detectado no navegador.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Quando o servidor detectou pela primeira vez que o script estava bloqueado
            $table->timestamp('nsfw_scan_blocked_at')->nullable()->after('remember_token');

            // Até quando o usuário fica travado para uploads de imagem
            // (nsfw_scan_blocked_at + 24h). NULL = não está travado.
            $table->timestamp('nsfw_scan_unlocks_at')->nullable()->after('nsfw_scan_blocked_at');

            $table->index('nsfw_scan_unlocks_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['nsfw_scan_unlocks_at']);
            $table->dropColumn(['nsfw_scan_blocked_at', 'nsfw_scan_unlocks_at']);
        });
    }
};
