<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'owner_type')) {
                $table->string('owner_type')->default('platform')->after('author_id');
            }
            if (! Schema::hasColumn('courses', 'company_profile_id')) {
                $table->foreignId('company_profile_id')
                    ->nullable()
                    ->after('owner_type')
                    ->constrained('company_profiles')
                    ->cascadeOnDelete();
            }
            if (! Schema::hasColumn('courses', 'visibility')) {
                $table->string('visibility')->default('public')->after('company_profile_id');
            }
            if (! Schema::hasColumn('courses', 'access_token')) {
                $table->string('access_token', 48)->nullable()->unique()->after('visibility');
            }
            if (! Schema::hasColumn('courses', 'job_listing_id')) {
                $table->foreignId('job_listing_id')
                    ->nullable()
                    ->after('access_token')
                    ->constrained('job_listings')
                    ->nullOnDelete();
            }

            $table->index(['owner_type', 'company_profile_id'], 'courses_owner_company_idx');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('courses_owner_company_idx');

            if (Schema::hasColumn('courses', 'job_listing_id')) {
                $table->dropConstrainedForeignId('job_listing_id');
            }
            if (Schema::hasColumn('courses', 'access_token')) {
                $table->dropColumn('access_token');
            }
            if (Schema::hasColumn('courses', 'visibility')) {
                $table->dropColumn('visibility');
            }
            if (Schema::hasColumn('courses', 'company_profile_id')) {
                $table->dropConstrainedForeignId('company_profile_id');
            }
            if (Schema::hasColumn('courses', 'owner_type')) {
                $table->dropColumn('owner_type');
            }
        });
    }
};
