<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nome pode ficar opcional (perfis podem ser preenchidos depois)
            $table->string('name')->nullable()->change();

            $table->string('username', 60)->nullable()->unique()->after('name');
            $table->enum('type', ['candidate', 'company', 'admin'])->default('candidate')->after('username');
            $table->string('avatar_path')->nullable()->after('type');
            $table->string('cover_path')->nullable()->after('avatar_path');
            $table->string('headline', 160)->nullable()->after('cover_path');
            $table->string('location', 120)->nullable()->after('headline');
            $table->boolean('is_verified')->default(false)->after('location');
            $table->boolean('open_to_work')->default(false)->after('is_verified');

            $table->index('type');
            $table->index('open_to_work');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['open_to_work']);
            $table->dropColumn([
                'username', 'type', 'avatar_path', 'cover_path',
                'headline', 'location', 'is_verified', 'open_to_work',
            ]);
        });
    }
};
