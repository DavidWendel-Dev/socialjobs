<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * BadgesSeeder — cria o conjunto inicial de conquistas (badges) do SocialJobs.
 *
 * Cada badge tem code (identificador único), name (rótulo em pt_BR),
 * icon (emoji/ícone) e description (texto motivacional).
 */
class BadgesSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('badges')) {
            $this->command?->warn('[BadgesSeeder] Tabela "badges" não existe — pulando.');
            return;
        }

        // Cada item: [code, name, icon, description]
        $badges = [
            ['profile_complete',  'Perfil Completo',       '🧩', 'Preencheu todo o perfil profissional.'],
            ['email_verified',    'E-mail Verificado',     '✉️', 'Confirmou o e-mail cadastrado.'],
            ['first_post',        'Primeiro Post',         '📝', 'Publicou o primeiro conteúdo na rede.'],
            ['first_application', 'Primeira Candidatura',  '🚀', 'Se candidatou a uma vaga pela primeira vez.'],
            ['first_hire',        'Primeira Contratação',  '🎯', 'Fechou a primeira contratação via SocialJobs.'],
            ['top_commenter',     'Comentarista Destaque', '💬', 'Está entre os que mais engajam nos comentários.'],
            ['helpful_neighbor',  'Vizinho Prestativo',    '🤝', 'Ajudou colegas com dicas e recomendações.'],
            ['hired',             'Contratado',            '🏆', 'Foi contratado através da plataforma.'],
            ['course_finisher',   'Concluiu um Curso',     '🎓', 'Finalizou um curso completo na plataforma.'],
            ['quiz_ace',          'Craque nos Quizzes',    '🧠', 'Acertou 100% em pelo menos um quiz.'],
            ['interview_beast',   'Fera nas Entrevistas',  '🦁', 'Alcançou nota alta no simulador de entrevistas.'],
            ['streak_7',          'Sequência de 7 Dias',   '🔥', 'Acessou a plataforma 7 dias seguidos.'],
            ['streak_30',         'Sequência de 30 Dias',  '🔥', 'Manteve 30 dias consecutivos de atividade.'],
            ['endorsed',          'Endossado',             '⭐', 'Recebeu endossos de outros profissionais.'],
            ['connector',         'Conector',              '🌐', 'Fez muitas conexões relevantes na rede.'],
            ['mentor',            'Mentor',                '🧭', 'Compartilhou conhecimento como mentor.'],
        ];

        $columns = Schema::getColumnListing('badges');
        $has = static fn (string $col): bool => in_array($col, $columns, true);

        $now  = now();
        $rows = [];

        foreach ($badges as [$code, $name, $icon, $description]) {
            $row = [];
            if ($has('code'))        { $row['code']        = $code; }
            if ($has('name'))        { $row['name']        = $name; }
            if ($has('icon'))        { $row['icon']        = $icon; }
            if ($has('description')) { $row['description'] = $description; }
            if ($has('created_at'))  { $row['created_at']  = $now; }
            if ($has('updated_at'))  { $row['updated_at']  = $now; }
            $rows[] = $row;
        }

        if ($has('code')) {
            DB::table('badges')->upsert(
                $rows,
                ['code'],
                array_values(array_filter(['name', 'icon', 'description', 'updated_at'], $has))
            );
        } else {
            DB::table('badges')->insert($rows);
        }

        $this->command?->info(sprintf('[BadgesSeeder] %d badges processadas.', count($rows)));
    }
}
