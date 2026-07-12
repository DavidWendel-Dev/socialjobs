<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona os campos que precisamos para o fluxo de cadastro
     * de empresa brasileira com CNPJ:
     *  - cnpj (só dígitos, único)
     *  - trade_name (nome fantasia)
     *  - phone (contato)
     *  - address (JSON com logradouro, número, bairro, cidade, UF, CEP)
     */
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('cnpj', 14)->nullable()->unique()->after('user_id');
            $table->string('trade_name', 255)->nullable()->after('legal_name');
            $table->string('phone', 30)->nullable()->after('website');
            $table->json('address')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropUnique(['cnpj']);
            $table->dropColumn(['cnpj', 'trade_name', 'phone', 'address']);
        });
    }
};
