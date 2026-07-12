<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SkillAssessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Cria/atualiza o CATÁLOGO de testes de proficiência.
 *
 * Não cria questões — elas são geradas dinamicamente pela Groq quando
 * o candidato inicia cada teste. Este seeder cuida apenas dos metadados
 * (título, cor, ícone, categoria, dificuldade).
 */
class SkillAssessmentsCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            // ============================================================
            // Ferramentas de Escritório
            // ============================================================
            ['title' => 'Excel Intermediário',
             'category' => 'Ferramentas de Escritório', 'difficulty' => 'intermediate',
             'icon' => 'sparkles', 'color' => 'brand',
             'duration' => 18, // fórmulas exigem cálculo mental
             'short_description' => 'Fórmulas, PROCV, tabelas dinâmicas e formatação avançada.',
             'description' => 'Avalia conhecimentos práticos em Excel para uso profissional diário. Cobre fórmulas SOMASE/CONT.SE/PROCV, referências absolutas, formatação condicional e criação de tabelas dinâmicas.'],

            ['title' => 'Word Profissional',
             'category' => 'Ferramentas de Escritório', 'difficulty' => 'basic',
             'icon' => 'briefcase', 'color' => 'blue',
             'short_description' => 'Formatação, estilos, revisão colaborativa e documentos oficiais.',
             'description' => 'Testa habilidades de produção de documentos profissionais no Word: estilos, sumários, tabelas, controle de revisões, cabeçalho e rodapé.'],

            ['title' => 'PowerPoint Executivo',
             'category' => 'Ferramentas de Escritório', 'difficulty' => 'intermediate',
             'icon' => 'sparkles', 'color' => 'amber',
             'short_description' => 'Design de slides, storytelling visual e apresentações de impacto.',
             'description' => 'Avalia como criar apresentações profissionais claras e impactantes, com boa hierarquia visual, uso correto de gráficos e estrutura narrativa.'],

            ['title' => 'Google Workspace',
             'category' => 'Ferramentas de Escritório', 'difficulty' => 'basic',
             'icon' => 'briefcase', 'color' => 'blue',
             'short_description' => 'Docs, Sheets, Drive, Meet e colaboração em tempo real.',
             'description' => 'Ecossistema Google no ambiente corporativo: compartilhamento, permissões, colaboração simultânea e integração entre ferramentas.'],

            // ============================================================
            // Comunicação & Idiomas
            // ============================================================
            ['title' => 'Língua Portuguesa Profissional',
             'category' => 'Comunicação', 'difficulty' => 'intermediate',
             'icon' => 'academic', 'color' => 'amber',
             'short_description' => 'Ortografia, concordância, crase e escrita corporativa.',
             'description' => 'Avalia domínio do português culto aplicado ao ambiente de trabalho: e-mails, atas, ofícios, redação clara e sem erros comuns.'],

            ['title' => 'Comunicação Escrita Corporativa',
             'category' => 'Comunicação', 'difficulty' => 'basic',
             'icon' => 'message', 'color' => 'blue',
             'short_description' => 'E-mails, WhatsApp corporativo, atas e comunicados internos.',
             'description' => 'Foco em comunicação profissional escrita: escolha de canal, tom apropriado, clareza, formatação e etiqueta corporativa.'],

            ['title' => 'Inglês Instrumental',
             'category' => 'Idiomas', 'difficulty' => 'basic',
             'icon' => 'academic', 'color' => 'blue',
             'short_description' => 'Vocabulário e gramática essenciais para leitura profissional.',
             'description' => 'Nível A2-B1 de inglês voltado para leitura de e-mails, contratos, manuais e sites profissionais. Sem foco em conversação.'],

            // ============================================================
            // Vendas & Atendimento
            // ============================================================
            ['title' => 'Atendimento ao Cliente',
             'category' => 'Vendas & Atendimento', 'difficulty' => 'basic',
             'icon' => 'message', 'color' => 'brand',
             'short_description' => 'Escuta ativa, resolução de conflitos e Código de Defesa do Consumidor.',
             'description' => 'Habilidades essenciais para atendimento presencial, telefônico e digital. Cobre empatia, técnica LATTE, CDC e recuperação de clientes insatisfeitos.'],

            ['title' => 'Técnicas de Vendas Consultivas',
             'category' => 'Vendas & Atendimento', 'difficulty' => 'intermediate',
             'icon' => 'trophy', 'color' => 'amber',
             'short_description' => 'SPIN Selling, quebra de objeções e fechamento.',
             'description' => 'Foco em vendas complexas B2B e B2C: prospecção, qualificação de leads, argumentação, superação de objeções e técnicas de fechamento éticas.'],

            // ============================================================
            // Administrativo & Finanças
            // ============================================================
            ['title' => 'Rotinas Administrativas',
             'category' => 'Administrativo', 'difficulty' => 'basic',
             'icon' => 'briefcase', 'color' => 'accent',
             'short_description' => 'Documentos, arquivamento, agenda e controles internos.',
             'description' => 'Base do trabalho administrativo: protocolo, arquivamento, contas a pagar/receber, agenda executiva e etiqueta corporativa.'],

            ['title' => 'Educação Financeira Pessoal',
             'category' => 'Finanças', 'difficulty' => 'basic',
             'icon' => 'trophy', 'color' => 'brand',
             'short_description' => 'Orçamento, dívidas, poupança e investimentos iniciais.',
             'description' => 'Avaliação de conhecimentos práticos sobre finanças pessoais: como montar orçamento, sair de dívidas, poupar e começar a investir com segurança.'],

            ['title' => 'Fluxo de Caixa Empresarial',
             'category' => 'Finanças', 'difficulty' => 'intermediate',
             'icon' => 'briefcase', 'color' => 'accent',
             'duration' => 20, // envolve cálculos, exige mais tempo
             'short_description' => 'Contas a pagar/receber, capital de giro e DRE básico.',
             'description' => 'Gestão financeira operacional: previsão de caixa, conciliação bancária, prazo médio de pagamento/recebimento e leitura básica de DRE.'],

            // ============================================================
            // Carreira & Soft Skills
            // ============================================================
            ['title' => 'Currículo e ATS',
             'category' => 'Carreira', 'difficulty' => 'basic',
             'icon' => 'academic', 'color' => 'brand',
             'duration' => 8, // teste mais rápido, boas práticas objetivas
             'short_description' => 'Estrutura de CV, palavras-chave e passar em sistemas de triagem.',
             'description' => 'Como criar um currículo que passe pelos filtros automáticos (ATS) e chame atenção do recrutador: hierarquia, palavras-chave, verbos de ação, formato.'],

            ['title' => 'Entrevistas de Emprego',
             'category' => 'Carreira', 'difficulty' => 'basic',
             'icon' => 'mic', 'color' => 'blue',
             'short_description' => 'Método STAR, entrevista comportamental e negociação salarial.',
             'description' => 'Domine as etapas da entrevista: preparação, respostas com o método STAR, gestão da ansiedade, perguntas para fazer e negociação do pacote.'],

            ['title' => 'LinkedIn na Prática',
             'category' => 'Carreira', 'difficulty' => 'basic',
             'icon' => 'user', 'color' => 'blue',
             'short_description' => 'Perfil otimizado, networking e busca ativa de vagas.',
             'description' => 'Como usar o LinkedIn como ferramenta profissional: perfil que converte, headline, sobre, publicações, InMail, algoritmo e Social Selling Index.'],

            ['title' => 'Inteligência Emocional no Trabalho',
             'category' => 'Carreira', 'difficulty' => 'intermediate',
             'icon' => 'user', 'color' => 'accent',
             'short_description' => 'Autoconsciência, empatia, autocontrole e feedback difícil.',
             'description' => 'Habilidades sócio-emocionais aplicadas ao trabalho: identificar emoções, reagir a críticas, dar feedback construtivo, gerenciar conflitos.'],

            ['title' => 'Produtividade e Gestão do Tempo',
             'category' => 'Carreira', 'difficulty' => 'basic',
             'icon' => 'sparkles', 'color' => 'brand',
             'short_description' => 'Pomodoro, GTD, matriz de Eisenhower e priorização.',
             'description' => 'Técnicas comprovadas de produtividade pessoal: identificar prioridades reais, evitar distrações, usar métodos como Pomodoro e GTD.'],

            // ============================================================
            // Marketing
            // ============================================================
            ['title' => 'Marketing Digital',
             'category' => 'Marketing', 'difficulty' => 'basic',
             'icon' => 'sparkles', 'color' => 'accent',
             'short_description' => 'SEO, funil de vendas, tráfego pago e métricas essenciais.',
             'description' => 'Fundamentos do marketing digital: jornada do cliente, funil, SEO on-page, tráfego pago (Google/Meta Ads), métricas como CAC, LTV, CTR e ROAS.'],

            // ============================================================
            // Tecnologia
            // ============================================================
            ['title' => 'ChatGPT e IA no Trabalho',
             'category' => 'Tecnologia', 'difficulty' => 'basic',
             'icon' => 'sparkles', 'color' => 'brand',
             'short_description' => 'Prompts eficazes, produtividade e ética no uso de IA.',
             'description' => 'Como usar ChatGPT e outras IAs generativas com eficiência no trabalho: engenharia de prompt, casos de uso, limites, riscos de vazamento e ética.'],

            ['title' => 'Lógica de Programação',
             'category' => 'Tecnologia', 'difficulty' => 'basic',
             'icon' => 'sparkles', 'color' => 'blue',
             'duration' => 20, // rastrear código exige mais tempo
             'short_description' => 'Variáveis, condicionais, loops e pensamento computacional.',
             'description' => 'Base para quem quer entrar em tecnologia: raciocínio lógico aplicado a programação, com algoritmos simples, condicionais, laços e funções.'],

            // ============================================================
            // Legislação
            // ============================================================
            ['title' => 'LGPD na Prática',
             'category' => 'Legislação', 'difficulty' => 'intermediate',
             'icon' => 'briefcase', 'color' => 'rose',
             'short_description' => 'Princípios, direitos do titular e obrigações da empresa.',
             'description' => 'Aplicação prática da Lei Geral de Proteção de Dados no dia a dia corporativo: base legal, direitos dos titulares, incidentes e boas práticas.'],

            ['title' => 'CLT para Profissionais',
             'category' => 'Legislação', 'difficulty' => 'basic',
             'icon' => 'briefcase', 'color' => 'rose',
             'short_description' => 'Jornada, férias, 13º, licenças e direitos trabalhistas.',
             'description' => 'Direitos e deveres do trabalhador CLT: jornada de trabalho, hora extra, férias, 13º, licenças (maternidade, saúde), rescisão e verbas rescisórias.'],
        ];

        foreach ($catalog as $item) {
            $slug = Str::slug($item['title']);

            // Duração e XP variam por dificuldade. Alguns testes têm ajustes
            // específicos (override via chave 'duration' ou 'xp' no item).
            [$durationDefault, $xpDefault] = match ($item['difficulty']) {
                'basic'        => [10, 120],   // mais direto, +120 XP
                'intermediate' => [15, 150],   // padrão, +150 XP
                'advanced'     => [22, 200],   // mais reflexão, +200 XP
                default        => [15, 150],
            };

            SkillAssessment::updateOrCreate(
                ['slug' => $slug],
                [
                    'title'             => $item['title'],
                    'category'          => $item['category'],
                    'short_description' => $item['short_description'],
                    'description'       => $item['description'],
                    'difficulty'        => $item['difficulty'],
                    'icon'              => $item['icon'],
                    'color'             => $item['color'],
                    'duration_minutes'  => (int) ($item['duration'] ?? $durationDefault),
                    'passing_score'     => 70,
                    'xp_reward'         => (int) ($item['xp'] ?? $xpDefault),
                    'is_active'         => true,
                ]
            );
        }
    }
}
