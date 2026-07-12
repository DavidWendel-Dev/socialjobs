<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona coluna `order` para ordenar mídias dentro de um post
     * (ex.: post com 3 fotos aparece na ordem que o autor escolheu).
     */
    public function up(): void
    {
        Schema::table('post_media', function (Blueprint $table) {
            $table->unsignedTinyInteger('order')->default(0)->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('post_media', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
