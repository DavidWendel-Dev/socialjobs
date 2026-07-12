<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona flags forenses de integridade nas tentativas de skill assessment.
 *
 * Esses dados NÃO influem no score, mas ficam registrados para:
 * - Detectar padrões suspeitos (usar ChatGPT, colar respostas)
 * - Auto-anular tentativas com muitas violações
 * - Mostrar "asterisco" no badge para recrutador ver
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skill_assessment_attempts', function (Blueprint $table) {
            // Quantas vezes o candidato saiu da aba (visibilitychange hidden)
            $table->unsignedSmallInteger('tab_leaves')->default(0)->after('duration_seconds');

            // Quantas vezes tentou copiar (Ctrl+C, botão direito, etc.)
            $table->unsignedSmallInteger('copy_attempts')->default(0)->after('tab_leaves');

            // Quantas vezes abriu DevTools
            $table->unsignedSmallInteger('devtools_opens')->default(0)->after('copy_attempts');

            // "clean" | "suspicious" | "auto_terminated"
            // - clean: tudo dentro do normal
            // - suspicious: teve algumas violações mas terminou normal (badge com asterisco)
            // - auto_terminated: sistema finalizou por violação (3+ saídas da aba)
            $table->enum('integrity_status', ['clean', 'suspicious', 'auto_terminated'])
                  ->default('clean')
                  ->after('devtools_opens');
        });
    }

    public function down(): void
    {
        Schema::table('skill_assessment_attempts', function (Blueprint $table) {
            $table->dropColumn(['tab_leaves', 'copy_attempts', 'devtools_opens', 'integrity_status']);
        });
    }
};
