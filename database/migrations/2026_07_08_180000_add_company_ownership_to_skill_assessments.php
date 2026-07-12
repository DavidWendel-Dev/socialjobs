<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permite que empresas criem seus próprios Skill Assessments customizados.
 *
 * - owner_type: 'platform' (catálogo padrão) ou 'company' (criado por empresa)
 * - company_profile_id: quem é a dona (quando owner_type = 'company')
 * - visibility: 'public' aparece no catálogo, 'invite_only' só via convite
 * - job_listing_id: teste opcionalmente amarrado a uma vaga
 * - created_by_user_id: usuário que criou (auditoria)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skill_assessments', function (Blueprint $table) {
            $table->string('owner_type', 20)->default('platform')->after('id');
            $table->foreignId('company_profile_id')
                  ->nullable()
                  ->after('owner_type')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('visibility', 20)->default('public')->after('is_active');
            $table->foreignId('job_listing_id')
                  ->nullable()
                  ->after('visibility')
                  ->constrained()
                  ->nullOnDelete();
            $table->foreignId('created_by_user_id')
                  ->nullable()
                  ->after('job_listing_id')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->index(['owner_type', 'company_profile_id'], 'sa_owner_company_idx');
        });
    }

    public function down(): void
    {
        Schema::table('skill_assessments', function (Blueprint $table) {
            $table->dropIndex('sa_owner_company_idx');
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropConstrainedForeignId('job_listing_id');
            $table->dropColumn('visibility');
            $table->dropConstrainedForeignId('company_profile_id');
            $table->dropColumn('owner_type');
        });
    }
};
