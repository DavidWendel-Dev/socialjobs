<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona contagem de visualizações em posts e users (perfis).
 *
 * Estratégia: contador denormalizado no próprio registro para leitura O(1)
 * em listagens (feed com milhares de posts). Incremento único por sessão via
 * cache/session no controller para evitar inflar com refreshes.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedBigInteger('views_count')->default(0)->after('link_preview');
            $table->index('views_count');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('profile_views_count')->default(0);
            $table->index('profile_views_count');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['views_count']);
            $table->dropColumn('views_count');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['profile_views_count']);
            $table->dropColumn('profile_views_count');
        });
    }
};
