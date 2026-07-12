<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * SkillsSeeder — popula o catálogo de habilidades usadas por candidatos e vagas.
 *
 * Cobre programação, dados, cloud/DevOps, design, marketing, vendas, gestão
 * e idiomas — cerca de 120 skills variadas.
 */
class SkillsSeeder extends Seeder
{
    public function run(): void
    {
        // Se ainda não existe tabela `skills`, apenas informa e sai.
        if (! Schema::hasTable('skills')) {
            $this->command?->warn('[SkillsSeeder] Tabela "skills" não existe — pulando.');
            return;
        }

        // Mapa: categoria => [skill, skill, ...]
        $catalog = [
            'Programação' => [
                'Laravel', 'PHP', 'JavaScript', 'TypeScript', 'React', 'Vue.js', 'Angular',
                'Node.js', 'Python', 'Django', 'FastAPI', 'Ruby', 'Ruby on Rails', 'Go',
                'Rust', 'Java', 'Spring Boot', 'Kotlin', 'Swift', 'C#', '.NET',
                'HTML5', 'CSS3', 'Tailwind CSS', 'Bootstrap', 'Livewire', 'Alpine.js',
                'GraphQL', 'REST APIs', 'WebSockets',
            ],
            'Dados' => [
                'SQL', 'PostgreSQL', 'MySQL', 'MongoDB', 'Redis', 'ETL', 'Power BI',
                'Tableau', 'Excel Avançado', 'Pandas', 'NumPy', 'Apache Spark',
                'BigQuery', 'Data Warehousing', 'dbt',
            ],
            'Cloud e DevOps' => [
                'AWS', 'Google Cloud (GCP)', 'Azure', 'Docker', 'Kubernetes',
                'Terraform', 'CI/CD', 'Linux', 'Nginx', 'Git', 'GitHub Actions',
                'GitLab CI', 'Ansible', 'Prometheus', 'Grafana',
            ],
            'Design' => [
                'Figma', 'Adobe XD', 'Photoshop', 'Illustrator', 'After Effects',
                'UX Research', 'Design System', 'Prototipagem', 'Design de Interação',
                'Motion Design',
            ],
            'Marketing' => [
                'SEO', 'Google Ads', 'Meta Ads', 'Copywriting', 'Growth Marketing',
                'Email Marketing', 'Google Analytics', 'HubSpot', 'RD Station',
                'Inbound Marketing', 'Marketing de Conteúdo', 'Branding',
            ],
            'Vendas' => [
                'Prospecção Ativa', 'Cold Call', 'CRM', 'Salesforce', 'Pipedrive',
                'Negociação', 'Fechamento', 'SPIN Selling', 'Customer Success',
                'Account Management',
            ],
            'Gestão' => [
                'Scrum', 'Kanban', 'OKR', 'Liderança', 'People Management', 'PMBOK',
                'Gestão de Projetos', 'Gestão de Produto', 'Business Analysis',
                'Change Management',
            ],
            'Idiomas' => [
                'Inglês', 'Espanhol', 'Francês', 'Alemão', 'Mandarim',
                'Italiano', 'Japonês', 'Português',
            ],
        ];

        // Descobre colunas disponíveis para não quebrar caso o schema mude.
        $columns = Schema::getColumnListing('skills');
        $has = static fn (string $col): bool => in_array($col, $columns, true);

        $rows = [];
        $now  = now();

        foreach ($catalog as $category => $skills) {
            foreach ($skills as $skill) {
                $row = [];
                if ($has('name'))       { $row['name']       = $skill; }
                if ($has('slug'))       { $row['slug']       = Str::slug($skill); }
                if ($has('category'))   { $row['category']   = $category; }
                if ($has('created_at')) { $row['created_at'] = $now; }
                if ($has('updated_at')) { $row['updated_at'] = $now; }
                $rows[] = $row;
            }
        }

        // upsert por slug (evita duplicatas em reexecução)
        if ($has('slug')) {
            DB::table('skills')->upsert(
                $rows,
                ['slug'],
                array_values(array_filter(['name', 'category', 'updated_at'], $has))
            );
        } else {
            DB::table('skills')->insert($rows);
        }

        $this->command?->info(sprintf('[SkillsSeeder] %d skills processadas.', count($rows)));
    }
}
