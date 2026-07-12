<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * AdminUserSeeder — cria/atualiza o usuário administrador principal
 * e atribui o role `admin` via Spatie Permission.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Descobre quais colunas o schema atual expõe (o schema real
        // será definido pelo outro agente).
        $columns = Schema::hasTable('users')
            ? Schema::getColumnListing('users')
            : [];

        $has = static fn (string $col): bool => in_array($col, $columns, true);

        // Dados base do admin — sobrescreva via ENV em produção
        $email    = env('ADMIN_EMAIL', 'admin@example.com');
        $password = Hash::make(env('ADMIN_PASSWORD', 'change-me-strong-password'));

        $attributes = [];
        if ($has('name'))         { $attributes['name']         = 'Administrador'; }
        if ($has('username'))     { $attributes['username']     = 'admin'; }
        if ($has('type'))         { $attributes['type']         = 'admin'; }
        if ($has('is_verified'))  { $attributes['is_verified']  = true; }
        if ($has('email_verified_at')) { $attributes['email_verified_at'] = now(); }
        if ($has('password'))     { $attributes['password']     = $password; }

        /** @var User $admin */
        $admin = User::updateOrCreate(
            ['email' => $email],
            $attributes
        );

        // Atribui role admin se o Spatie estiver disponível
        if (Schema::hasTable('roles') && class_exists(Role::class)) {
            Role::findOrCreate('admin', 'web');
            if (method_exists($admin, 'assignRole')) {
                $admin->assignRole('admin');
            }
        }

        $this->command?->info("[AdminUserSeeder] admin garantido: {$email}");
    }
}
