<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder — orquestra a ordem de execução dos seeders do SocialJobs.
 *
 *   1. RolesAndPermissionsSeeder (base de autorização via Spatie)
 *   2. SkillsSeeder              (catálogo de skills)
 *   3. BadgesSeeder              (conquistas)
 *   4. AdminUserSeeder           (admin principal)
 *   5. DemoContentSeeder         (conteúdo de exemplo)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SkillsSeeder::class,
            BadgesSeeder::class,
            AdminUserSeeder::class,
            DemoContentSeeder::class,
        ]);
    }
}
