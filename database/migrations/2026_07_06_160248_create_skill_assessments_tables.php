<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sistema de "Skill Assessments" (testes de proficiência).
 *
 * - skill_assessments: catálogo dos testes disponíveis (Excel, Vendas, etc.)
 * - skill_assessment_questions: as questões (options + gabarito + explicação)
 * - skill_assessment_attempts: cada tentativa que o usuário fez
 *
 * A pontuação exibida no perfil/CV é sempre o MELHOR score em tentativas passadas
 * (>= passing_score). O front-end obtém via SkillAssessmentService::bestScoresFor().
 */
return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // Catálogo dos testes
        // ============================================================
        Schema::create('skill_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);                 // ex: "Excel Intermediário"
            $table->string('slug', 140)->unique();        // ex: excel-intermediario
            $table->string('category', 60);               // Administrativo, Vendas, Tech...
            $table->string('short_description', 220);     // uma linha para o card
            $table->text('description')->nullable();      // texto longo na página de detalhe
            $table->enum('difficulty', ['basic', 'intermediate', 'advanced'])
                  ->default('intermediate');
            $table->string('icon', 40)->default('sparkles'); // nome do x-icon
            $table->string('color', 30)->default('brand');   // brand|blue|amber|accent|rose
            $table->unsignedSmallInteger('duration_minutes')->default(15);
            $table->unsignedTinyInteger('passing_score')->default(70); // % para "aprovar"
            $table->unsignedSmallInteger('xp_reward')->default(150);   // XP concedido ao passar
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'category']);
        });

        // ============================================================
        // Questões — bancos por teste
        // Cada questão tem 4 opções (armazenadas em json) e correct_index (0..3)
        // ============================================================
        Schema::create('skill_assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_assessment_id')
                  ->constrained('skill_assessments')
                  ->cascadeOnDelete();
            $table->text('statement');
            $table->json('options');                     // 4 alternativas
            $table->unsignedTinyInteger('correct_index'); // 0..3
            $table->text('explanation')->nullable();     // aparece após responder
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['skill_assessment_id', 'position']);
        });

        // ============================================================
        // Tentativas do usuário
        // ============================================================
        Schema::create('skill_assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_assessment_id')
                  ->constrained('skill_assessments')
                  ->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(0); // 0..100
            $table->boolean('passed')->default(false);
            $table->json('answers')->nullable();          // {question_id: chosen_index}
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'skill_assessment_id', 'score'], 'saa_user_skill_score_idx');
            // Para o "melhor score" por skill do user, usamos ORDER BY score DESC
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_assessment_attempts');
        Schema::dropIfExists('skill_assessment_questions');
        Schema::dropIfExists('skill_assessments');
    }
};
