<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Throwable;

/**
 * DemoContentSeeder — popula conteúdo de demonstração (empresas, candidatos,
 * vagas, posts, cursos, matrículas) usando Faker pt_BR.
 *
 * É defensivo: se uma tabela não existir (porque o outro agente ainda não
 * migrou), a etapa correspondente é pulada com um aviso — o seeder nunca
 * derruba o comando `db:seed`.
 */
class DemoContentSeeder extends Seeder
{
    private Generator $faker;

    public function run(): void
    {
        $this->faker = FakerFactory::create('pt_BR');

        // Ordem lógica: empresas → candidatos → vagas → posts → cursos → matrículas
        $companies      = $this->seedCompanies();
        $candidates     = $this->seedCandidates();
        $this->seedJobs($companies);
        $this->seedPosts(array_merge($companies, $candidates));
        $courses = $this->seedCourses();
        $this->seedEnrollments($candidates, $courses);
    }

    // -------------------------------------------------------------------------
    // Empresas
    // -------------------------------------------------------------------------
    /** @return array<int, int> IDs de usuários "empresa" */
    private function seedCompanies(): array
    {
        if (! Schema::hasTable('users')) {
            $this->command?->warn('[DemoContentSeeder] Tabela users ausente — pulando empresas.');
            return [];
        }

        $definitions = [
            [
                'name'        => 'TechAki Ltda',
                'email'       => 'contato@techaki.com.br',
                'username'    => 'techaki',
                'description' => 'Studio de desenvolvimento de software focado em produtos digitais escaláveis.',
                'industry'    => 'Tecnologia',
            ],
            [
                'name'        => 'Digital Farm SA',
                'email'       => 'rh@digitalfarm.com.br',
                'username'    => 'digitalfarm',
                'description' => 'Agência full-service de marketing digital e performance para pequenas e médias empresas.',
                'industry'    => 'Marketing Digital',
            ],
        ];

        $companyUserIds = [];

        foreach ($definitions as $def) {
            $userAttrs = $this->userAttributes([
                'name'     => $def['name'],
                'username' => $def['username'],
                'type'     => 'company',
                'password' => Hash::make('Empresa!2026'),
            ]);

            $user = User::updateOrCreate(['email' => $def['email']], $userAttrs);

            if (class_exists(Role::class) && Schema::hasTable('roles') && method_exists($user, 'assignRole')) {
                Role::findOrCreate('company_owner', 'web');
                try { $user->assignRole('company_owner'); } catch (Throwable) {}
            }

            $this->createCompanyProfile($user->id, $def);
            $companyUserIds[] = $user->id;
        }

        return $companyUserIds;
    }

    private function createCompanyProfile(int $userId, array $def): void
    {
        if (! Schema::hasTable('company_profiles')) {
            return;
        }

        $cols = Schema::getColumnListing('company_profiles');
        $has  = static fn (string $c): bool => in_array($c, $cols, true);

        $row = ['user_id' => $userId];
        if ($has('name'))        { $row['name']        = $def['name']; }
        if ($has('legal_name'))  { $row['legal_name']  = $def['name']; }
        if ($has('slug'))        { $row['slug']        = Str::slug($def['name']); }
        if ($has('description')) { $row['description'] = $def['description']; }
        if ($has('industry'))    { $row['industry']    = $def['industry']; }
        if ($has('website'))     { $row['website']     = 'https://' . Str::slug($def['name']) . '.com.br'; }
        if ($has('city'))        { $row['city']        = $this->faker->city(); }
        if ($has('state'))       { $row['state']       = $this->faker->stateAbbr(); }
        if ($has('created_at'))  { $row['created_at']  = now(); }
        if ($has('updated_at'))  { $row['updated_at']  = now(); }

        DB::table('company_profiles')->updateOrInsert(['user_id' => $userId], $row);
    }

    // -------------------------------------------------------------------------
    // Candidatos
    // -------------------------------------------------------------------------
    /** @return array<int, int> IDs de usuários candidatos */
    private function seedCandidates(): array
    {
        if (! Schema::hasTable('users')) {
            return [];
        }

        $ids = [];
        for ($i = 1; $i <= 8; $i++) {
            $firstName = $this->faker->firstName();
            $lastName  = $this->faker->lastName();
            $email     = Str::lower("{$firstName}.{$lastName}{$i}@example.com");

            $attrs = $this->userAttributes([
                'name'         => "{$firstName} {$lastName}",
                'username'     => Str::slug("{$firstName}-{$lastName}-{$i}"),
                'type'         => 'candidate',
                'password'     => Hash::make('Candidato!2026'),
                'headline'     => $this->faker->jobTitle() . ' apaixonado por resolver problemas',
                'location'     => $this->faker->city() . ', ' . $this->faker->stateAbbr(),
                'open_to_work' => $this->faker->boolean(70),
            ]);

            $user = User::updateOrCreate(['email' => $email], $attrs);

            if (class_exists(Role::class) && Schema::hasTable('roles') && method_exists($user, 'assignRole')) {
                Role::findOrCreate('candidate', 'web');
                try { $user->assignRole('candidate'); } catch (Throwable) {}
            }

            $this->createCandidateProfile($user->id, $firstName, $lastName);
            $this->attachRandomSkills($user->id);
            $this->createExperience($user->id);

            $ids[] = $user->id;
        }

        return $ids;
    }

    private function createCandidateProfile(int $userId, string $firstName, string $lastName): void
    {
        if (! Schema::hasTable('candidate_profiles')) {
            return;
        }

        $cols = Schema::getColumnListing('candidate_profiles');
        $has  = static fn (string $c): bool => in_array($c, $cols, true);

        $row = ['user_id' => $userId];
        if ($has('headline'))    { $row['headline']    = $this->faker->jobTitle(); }
        if ($has('bio'))         { $row['bio']         = $this->faker->paragraph(3); }
        if ($has('city'))        { $row['city']        = $this->faker->city(); }
        if ($has('state'))       { $row['state']       = $this->faker->stateAbbr(); }
        if ($has('phone'))       { $row['phone']       = $this->faker->cellphoneNumber(); }
        if ($has('birth_date'))  { $row['birth_date']  = $this->faker->dateTimeBetween('-45 years', '-20 years'); }
        if ($has('slug'))        { $row['slug']        = Str::slug("{$firstName}-{$lastName}-" . Str::random(4)); }
        if ($has('created_at'))  { $row['created_at']  = now(); }
        if ($has('updated_at'))  { $row['updated_at']  = now(); }

        DB::table('candidate_profiles')->updateOrInsert(['user_id' => $userId], $row);
    }

    private function attachRandomSkills(int $userId): void
    {
        // A tabela pivô pode se chamar candidate_profile_skill (nosso padrão),
        // candidate_skill, user_skills, etc.
        $pivotCandidates = ['candidate_profile_skill', 'candidate_skills', 'candidate_skill', 'user_skills', 'skill_user'];
        $pivot = null;
        foreach ($pivotCandidates as $t) {
            if (Schema::hasTable($t)) { $pivot = $t; break; }
        }
        if ($pivot === null || ! Schema::hasTable('skills')) {
            return;
        }

        // Resolve candidate_profile_id (o pivô usa esta FK, não user_id)
        $candidateProfileId = null;
        if (Schema::hasTable('candidate_profiles')) {
            $candidateProfileId = DB::table('candidate_profiles')
                ->where('user_id', $userId)
                ->value('id');
        }

        $skillIds = DB::table('skills')->inRandomOrder()->limit(5)->pluck('id')->all();
        if (empty($skillIds)) {
            return;
        }

        $cols = Schema::getColumnListing($pivot);
        $has  = static fn (string $c): bool => in_array($c, $cols, true);

        foreach ($skillIds as $skillId) {
            $row = [];
            if ($has('candidate_profile_id') && $candidateProfileId) { $row['candidate_profile_id'] = $candidateProfileId; }
            if ($has('user_id'))      { $row['user_id']      = $userId; }
            if ($has('candidate_id')) { $row['candidate_id'] = $userId; }
            if ($has('skill_id'))     { $row['skill_id']     = $skillId; }
            if ($has('level'))        { $row['level']        = $this->faker->randomElement(['basic', 'intermediate', 'advanced']); }
            if ($has('created_at'))   { $row['created_at']   = now(); }
            if ($has('updated_at'))   { $row['updated_at']   = now(); }

            try {
                DB::table($pivot)->insert($row);
            } catch (Throwable) {
                // pula duplicatas
            }
        }
    }

    private function createExperience(int $userId): void
    {
        $table = null;
        foreach (['experiences', 'candidate_experiences', 'work_experiences'] as $t) {
            if (Schema::hasTable($t)) { $table = $t; break; }
        }
        if ($table === null) {
            return;
        }

        // Resolve candidate_profile_id (nossa FK)
        $candidateProfileId = null;
        if (Schema::hasTable('candidate_profiles')) {
            $candidateProfileId = DB::table('candidate_profiles')
                ->where('user_id', $userId)
                ->value('id');
        }

        $cols = Schema::getColumnListing($table);
        $has  = static fn (string $c): bool => in_array($c, $cols, true);

        $roleTitle = $this->faker->jobTitle();

        $row = [];
        if ($has('candidate_profile_id') && $candidateProfileId) { $row['candidate_profile_id'] = $candidateProfileId; }
        if ($has('user_id'))      { $row['user_id']      = $userId; }
        if ($has('candidate_id')) { $row['candidate_id'] = $userId; }
        if ($has('company'))      { $row['company']      = $this->faker->company(); }
        if ($has('company_name')) { $row['company_name'] = $this->faker->company(); }
        if ($has('position'))     { $row['position']     = $roleTitle; }
        if ($has('role'))         { $row['role']         = $roleTitle; }
        if ($has('title'))        { $row['title']        = $roleTitle; }
        if ($has('description'))  { $row['description']  = $this->faker->paragraph(2); }
        if ($has('start_date'))   { $row['start_date']   = $this->faker->dateTimeBetween('-8 years', '-2 years'); }
        if ($has('end_date'))     { $row['end_date']     = $this->faker->dateTimeBetween('-1 years', 'now'); }
        if ($has('is_current'))   { $row['is_current']   = false; }
        if ($has('current'))      { $row['current']      = false; }
        if ($has('created_at'))   { $row['created_at']   = now(); }
        if ($has('updated_at'))   { $row['updated_at']   = now(); }

        try { DB::table($table)->insert($row); } catch (Throwable) {}
    }

    // -------------------------------------------------------------------------
    // Vagas
    // -------------------------------------------------------------------------
    private function seedJobs(array $companyUserIds): void
    {
        $jobsTable = null;
        foreach (['job_listings', 'jobs', 'job_posts', 'vacancies'] as $t) {
            if (Schema::hasTable($t)) { $jobsTable = $t; break; }
        }
        // Cuidado: 'jobs' pode ser da fila. Verificamos se tem coluna typica.
        if ($jobsTable === 'jobs' && ! Schema::hasColumn('jobs', 'title')) {
            $jobsTable = null;
        }
        if ($jobsTable === null || empty($companyUserIds)) {
            $this->command?->warn('[DemoContentSeeder] Tabela de vagas indisponível — pulando vagas.');
            return;
        }

        $cols = Schema::getColumnListing($jobsTable);
        $has  = static fn (string $c): bool => in_array($c, $cols, true);

        // Mapa: user_id da empresa -> company_profile.id (caso o schema use FK para profile)
        $companyProfileIds = [];
        if (Schema::hasTable('company_profiles')) {
            $companyProfileIds = DB::table('company_profiles')
                ->whereIn('user_id', $companyUserIds)
                ->pluck('id', 'user_id')
                ->all();
        }

        $modalidades = ['remote', 'hybrid', 'onsite'];
        $contratos   = ['clt', 'pj', 'internship', 'freelance'];
        $seniorities = ['junior', 'mid', 'senior', 'lead'];

        foreach ($companyUserIds as $companyUserId) {
            for ($i = 1; $i <= 5; $i++) {
                $title = $this->faker->jobTitle() . ' ' . $this->faker->randomElement(['Pleno', 'Sênior', 'Júnior']);

                $row = [];
                if ($has('company_profile_id') && isset($companyProfileIds[$companyUserId])) {
                    $row['company_profile_id'] = $companyProfileIds[$companyUserId];
                }
                if ($has('company_id'))    { $row['company_id']    = $companyUserId; }
                if ($has('user_id'))       { $row['user_id']       = $companyUserId; }
                if ($has('title'))         { $row['title']         = $title; }
                if ($has('slug'))          { $row['slug']          = Str::slug($title . '-' . Str::random(6)); }
                if ($has('description'))   { $row['description']   = '<p>' . $this->faker->paragraphs(4, true) . '</p>'; }
                if ($has('requirements'))  { $row['requirements']  = $this->faker->paragraph(3); }
                if ($has('salary_min'))    { $row['salary_min']    = $this->faker->numberBetween(2000, 6000); }
                if ($has('salary_max'))    { $row['salary_max']    = $this->faker->numberBetween(7000, 20000); }
                if ($has('salary'))        { $row['salary']        = $this->faker->numberBetween(3000, 15000); }
                if ($has('modality'))      { $row['modality']      = $this->faker->randomElement($modalidades); }
                if ($has('contract_type')) { $row['contract_type'] = $this->faker->randomElement($contratos); }
                if ($has('seniority'))     { $row['seniority']     = $this->faker->randomElement($seniorities); }
                if ($has('location'))      { $row['location']      = $this->faker->city() . ', ' . $this->faker->stateAbbr(); }
                if ($has('city'))          { $row['city']          = $this->faker->city(); }
                if ($has('state'))         { $row['state']         = $this->faker->stateAbbr(); }
                if ($has('status'))        { $row['status']        = 'open'; }
                if ($has('is_active'))     { $row['is_active']     = true; }
                if ($has('published_at'))  { $row['published_at']  = now(); }
                if ($has('created_at'))    { $row['created_at']    = now(); }
                if ($has('updated_at'))    { $row['updated_at']    = now(); }

                try { DB::table($jobsTable)->insert($row); } catch (Throwable) {}
            }
        }
    }

    // -------------------------------------------------------------------------
    // Posts + reações
    // -------------------------------------------------------------------------
    private function seedPosts(array $userIds): void
    {
        if (! Schema::hasTable('posts') || empty($userIds)) {
            return;
        }
        $cols = Schema::getColumnListing('posts');
        $has  = static fn (string $c): bool => in_array($c, $cols, true);

        $postIds = [];
        for ($i = 0; $i < 20; $i++) {
            $row = [];
            if ($has('user_id'))     { $row['user_id']     = $this->faker->randomElement($userIds); }
            if ($has('content'))     { $row['content']     = $this->faker->paragraph(mt_rand(2, 5)); }
            if ($has('body'))        { $row['body']        = $this->faker->paragraph(mt_rand(2, 5)); }
            if ($has('type'))        { $row['type']        = 'text'; }
            if ($has('visibility'))  { $row['visibility']  = 'public'; }
            if ($has('created_at'))  { $row['created_at']  = now()->subDays(mt_rand(0, 30)); }
            if ($has('updated_at'))  { $row['updated_at']  = now(); }

            try {
                $postIds[] = DB::table('posts')->insertGetId($row);
            } catch (Throwable) {
                // ignora
            }
        }

        // Reações aleatórias
        $reactionTable = null;
        foreach (['reactions', 'post_reactions'] as $t) {
            if (Schema::hasTable($t)) { $reactionTable = $t; break; }
        }
        if ($reactionTable === null) {
            return;
        }

        $rc = Schema::getColumnListing($reactionTable);
        $rhas = static fn (string $c): bool => in_array($c, $rc, true);

        // Tipos de reação — devem bater EXATAMENTE com config/reactions.php e o enum da migration
        $tipos = ['like', 'love', 'celebrate', 'support', 'insightful', 'funny'];

        foreach ($postIds as $pid) {
            $qty = mt_rand(1, 6);
            for ($k = 0; $k < $qty; $k++) {
                $row = [];
                if ($rhas('user_id'))        { $row['user_id']        = $this->faker->randomElement($userIds); }
                if ($rhas('post_id'))        { $row['post_id']        = $pid; }
                if ($rhas('reactable_id'))   { $row['reactable_id']   = $pid; }
                if ($rhas('reactable_type')) { $row['reactable_type'] = 'App\\Models\\Post'; }
                if ($rhas('type'))           { $row['type']           = $this->faker->randomElement($tipos); }
                if ($rhas('reaction'))       { $row['reaction']       = $this->faker->randomElement($tipos); }
                if ($rhas('created_at'))     { $row['created_at']     = now(); }
                if ($rhas('updated_at'))     { $row['updated_at']     = now(); }

                try { DB::table($reactionTable)->insert($row); } catch (Throwable) {}
            }
        }
    }

    // -------------------------------------------------------------------------
    // Cursos + módulos + aulas
    // -------------------------------------------------------------------------
    /** @return array<int, int> IDs de cursos criados */
    private function seedCourses(): array
    {
        if (! Schema::hasTable('courses')) {
            $this->command?->warn('[DemoContentSeeder] Tabela courses ausente — pulando cursos.');
            return [];
        }

        $courseDefs = [
            [
                'title'   => 'Fundamentos de Laravel',
                'summary' => 'Aprenda os pilares do framework Laravel na prática.',
                'modules' => [
                    ['name' => 'Instalação e Ambiente',    'lessons' => 2],
                    ['name' => 'Rotas, Controllers e Views', 'lessons' => 2],
                    ['name' => 'Eloquent e Migrações',     'lessons' => 2],
                ],
            ],
            [
                'title'   => 'Entrevista Técnica: Como se preparar',
                'summary' => 'Guia completo para brilhar em entrevistas técnicas.',
                'modules' => [
                    ['name' => 'Antes da Entrevista', 'lessons' => 2],
                    ['name' => 'Durante e Depois',    'lessons' => 3],
                ],
            ],
        ];

        $cCols = Schema::getColumnListing('courses');
        $cHas  = static fn (string $c): bool => in_array($c, $cCols, true);

        $courseIds = [];

        foreach ($courseDefs as $def) {
            $row = [];
            if ($cHas('title'))       { $row['title']       = $def['title']; }
            if ($cHas('name'))        { $row['name']        = $def['title']; }
            if ($cHas('slug'))        { $row['slug']        = Str::slug($def['title']); }
            if ($cHas('summary'))     { $row['summary']     = $def['summary']; }
            if ($cHas('description')) { $row['description'] = $def['summary']; }
            if ($cHas('is_published')){ $row['is_published']= true; }
            if ($cHas('status'))      { $row['status']      = 'published'; }
            if ($cHas('created_at'))  { $row['created_at']  = now(); }
            if ($cHas('updated_at'))  { $row['updated_at']  = now(); }

            $courseId = null;
            try {
                if ($cHas('slug')) {
                    DB::table('courses')->updateOrInsert(['slug' => $row['slug']], $row);
                    $courseId = DB::table('courses')->where('slug', $row['slug'])->value('id');
                } else {
                    $courseId = DB::table('courses')->insertGetId($row);
                }
            } catch (Throwable) {
                continue;
            }

            if ($courseId) {
                $courseIds[] = $courseId;
                $this->seedCourseModules((int) $courseId, $def['modules']);
            }
        }

        return $courseIds;
    }

    private function seedCourseModules(int $courseId, array $modules): void
    {
        $modTable = null;
        foreach (['course_modules', 'modules'] as $t) {
            if (Schema::hasTable($t)) { $modTable = $t; break; }
        }
        if ($modTable === null) {
            return;
        }

        $lessonTable = null;
        foreach (['course_lessons', 'lessons'] as $t) {
            if (Schema::hasTable($t)) { $lessonTable = $t; break; }
        }

        $mCols = Schema::getColumnListing($modTable);
        $mHas  = static fn (string $c): bool => in_array($c, $mCols, true);

        foreach ($modules as $idx => $mod) {
            $row = [];
            if ($mHas('course_id'))  { $row['course_id']  = $courseId; }
            if ($mHas('title'))      { $row['title']      = $mod['name']; }
            if ($mHas('name'))       { $row['name']       = $mod['name']; }
            if ($mHas('position'))   { $row['position']   = $idx + 1; }
            if ($mHas('order'))      { $row['order']      = $idx + 1; }
            if ($mHas('created_at')) { $row['created_at'] = now(); }
            if ($mHas('updated_at')) { $row['updated_at'] = now(); }

            $moduleId = null;
            try {
                $moduleId = DB::table($modTable)->insertGetId($row);
            } catch (Throwable) {
                continue;
            }

            if ($moduleId && $lessonTable !== null) {
                $this->seedLessons($lessonTable, (int) $moduleId, (int) $courseId, (int) $mod['lessons']);
            }
        }
    }

    private function seedLessons(string $table, int $moduleId, int $courseId, int $qty): void
    {
        $cols = Schema::getColumnListing($table);
        $has  = static fn (string $c): bool => in_array($c, $cols, true);

        for ($i = 1; $i <= $qty; $i++) {
            $title = "Aula {$i}: " . $this->faker->sentence(4);

            $row = [];
            if ($has('course_id'))      { $row['course_id']      = $courseId; }
            if ($has('module_id'))      { $row['module_id']      = $moduleId; }
            if ($has('course_module_id')){ $row['course_module_id']= $moduleId; }
            if ($has('title'))          { $row['title']          = $title; }
            if ($has('name'))           { $row['name']           = $title; }
            if ($has('slug'))           { $row['slug']           = Str::slug($title . '-' . Str::random(4)); }
            if ($has('content'))        { $row['content']        = $this->faker->paragraph(3); }
            if ($has('body'))           { $row['body']           = $this->faker->paragraph(3); }
            if ($has('video_provider')) { $row['video_provider'] = 'youtube'; }
            if ($has('video_ref'))      { $row['video_ref']      = 'dQw4w9WgXcQ'; }
            if ($has('duration'))       { $row['duration']       = mt_rand(300, 1800); }
            if ($has('position'))       { $row['position']       = $i; }
            if ($has('order'))          { $row['order']          = $i; }
            if ($has('created_at'))     { $row['created_at']     = now(); }
            if ($has('updated_at'))     { $row['updated_at']     = now(); }

            try { DB::table($table)->insert($row); } catch (Throwable) {}
        }
    }

    // -------------------------------------------------------------------------
    // Matrículas
    // -------------------------------------------------------------------------
    private function seedEnrollments(array $candidateIds, array $courseIds): void
    {
        if (empty($candidateIds) || empty($courseIds)) {
            return;
        }
        $table = null;
        foreach (['enrollments', 'course_enrollments', 'course_user'] as $t) {
            if (Schema::hasTable($t)) { $table = $t; break; }
        }
        if ($table === null) {
            return;
        }

        $cols = Schema::getColumnListing($table);
        $has  = static fn (string $c): bool => in_array($c, $cols, true);

        $courseId = $courseIds[0];
        $selected = array_slice($candidateIds, 0, 3);

        foreach ($selected as $userId) {
            $row = [];
            if ($has('user_id'))      { $row['user_id']      = $userId; }
            if ($has('candidate_id')) { $row['candidate_id'] = $userId; }
            if ($has('course_id'))    { $row['course_id']    = $courseId; }
            if ($has('progress'))     { $row['progress']     = $this->faker->numberBetween(10, 95); }
            if ($has('status'))       { $row['status']       = 'in_progress'; }
            if ($has('enrolled_at'))  { $row['enrolled_at']  = now()->subDays(mt_rand(1, 20)); }
            if ($has('created_at'))   { $row['created_at']   = now(); }
            if ($has('updated_at'))   { $row['updated_at']   = now(); }

            try { DB::table($table)->insert($row); } catch (Throwable) {}
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    /**
     * Filtra atributos para conterem só colunas realmente existentes em `users`.
     */
    private function userAttributes(array $desired): array
    {
        $cols = Schema::getColumnListing('users');
        $has  = static fn (string $c): bool => in_array($c, $cols, true);

        $out = [];
        foreach ($desired as $k => $v) {
            if ($has($k)) {
                $out[$k] = $v;
            }
        }
        if ($has('email_verified_at') && ! array_key_exists('email_verified_at', $out)) {
            $out['email_verified_at'] = now();
        }
        if ($has('is_verified') && ! array_key_exists('is_verified', $out)) {
            $out['is_verified'] = true;
        }
        return $out;
    }
}
