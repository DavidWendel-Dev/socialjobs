<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Convites de empresa para candidatos fazerem um Skill Assessment.
 *
 * Fluxo:
 *  - Empresa cria o convite (email do candidato + assessment)
 *  - Sistema gera token, envia link /take/{token}
 *  - Candidato abre → status vira 'opened'
 *  - Candidato finaliza → status vira 'completed' e attempt_id é preenchido
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_assessment_id')
                  ->constrained('skill_assessments')
                  ->cascadeOnDelete();
            $table->foreignId('company_profile_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('job_application_id')
                  ->nullable()
                  ->constrained('applications')
                  ->nullOnDelete();
            $table->foreignId('candidate_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->string('candidate_email', 191);
            $table->string('token', 64)->unique();
            $table->string('status', 20)->default('pending'); // pending|opened|completed|expired
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('attempt_id')
                  ->nullable()
                  ->constrained('skill_assessment_attempts')
                  ->nullOnDelete();
            $table->timestamps();

            $table->index(['company_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_invitations');
    }
};
