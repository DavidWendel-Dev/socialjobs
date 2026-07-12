<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\JobListing;
use App\Models\User;
use OpenAI;
use OpenAI\Client;
use Throwable;

/**
 * Camada de IA — usa cliente OpenAI-compatible (endpoint configurável em config/ai.php).
 * Inclui um "guard prompt" injetado como mensagem system em toda chamada.
 */
class AiService
{
    private Client $client;

    public function __construct()
    {
        // Cria o cliente diretamente a partir de config/ai.php.
        // Assim funciona com qualquer endpoint OpenAI-compatible (Groq, OpenAI, Ollama...).
        $baseUri = (string) preg_replace('~^https?://~i', '', rtrim((string) config('ai.base_url'), '/'));

        $this->client = OpenAI::factory()
            ->withApiKey((string) config('ai.api_key'))
            ->withBaseUri($baseUri)
            ->withHttpClient(new \GuzzleHttp\Client([
                'timeout' => (int) config('ai.timeout', 60),
            ]))
            ->make();
    }

    /**
     * Gera um currículo profissional baseado nos dados que o candidato preenche
     * no formulário guiado. A IA NÃO inventa nada — ela apenas polirá os textos:
     *  - Gera "objective" a partir do cargo alvo + skills declaradas
     *  - Gera "summary" a partir das experiências + skills
     *  - Reescreve os "raw_bullets" das experiências em bullets profissionais
     *    (verbo no passado, resultado quantificado se o candidato mencionou)
     *  - Calcula "ats_score" e devolve "suggestions" de melhoria
     *
     * @param array $form Estrutura vinda do Livewire. Ver Assistant::generateResume().
     * @return array{ats_score:int, suggestions:array<int,string>, resume:array, final_markdown:string}
     */
    public function generateResume(array $form): array
    {
        $targetRole = trim($form['target_role'] ?? '');
        $roleLine = $targetRole !== '' ? "Vaga alvo: {$targetRole}." : 'Vaga alvo: não informada.';

        // Formulário estruturado enviado ao prompt como JSON para a IA polir
        $formPayload = [
            'contact' => [
                'full_name'  => trim($form['contact']['full_name'] ?? ''),
                'role_title' => trim($form['contact']['role_title'] ?? ''),
                'email'      => trim($form['contact']['email'] ?? ''),
                'phone'      => trim($form['contact']['phone'] ?? ''),
                'address'    => trim($form['contact']['address'] ?? ''),
                'linkedin'   => trim($form['contact']['linkedin'] ?? ''),
                'portfolio'  => trim($form['contact']['portfolio'] ?? ''),
                'birth_date' => trim($form['contact']['birth_date'] ?? ''),
            ],
            'target_role'   => $targetRole,
            'user_objective'=> trim($form['user_objective'] ?? ''),
            'user_summary'  => trim($form['user_summary'] ?? ''),
            'experiences'   => array_values(array_map(fn ($e) => [
                'role'         => trim($e['role'] ?? ''),
                'company'      => trim($e['company'] ?? ''),
                'period'       => trim($e['period'] ?? ''),
                'raw_activities' => trim($e['raw_activities'] ?? ''),
            ], (array) ($form['experiences'] ?? []))),
            'education'     => array_values(array_map(fn ($ed) => [
                'degree'      => trim($ed['degree'] ?? ''),
                'institution' => trim($ed['institution'] ?? ''),
                'period'      => trim($ed['period'] ?? ''),
            ], (array) ($form['education'] ?? []))),
            'skills' => [
                'hard' => array_values(array_filter(array_map('trim', (array) ($form['skills']['hard'] ?? [])))),
                'soft' => array_values(array_filter(array_map('trim', (array) ($form['skills']['soft'] ?? [])))),
            ],
            'languages'      => array_values(array_map(fn ($l) => [
                'language' => trim($l['language'] ?? ''),
                'level'    => trim($l['level'] ?? ''),
            ], (array) ($form['languages'] ?? []))),
            'certifications' => array_values(array_map(fn ($c) => [
                'name'   => trim($c['name'] ?? ''),
                'issuer' => trim($c['issuer'] ?? ''),
                'year'   => trim($c['year'] ?? ''),
            ], (array) ($form['certifications'] ?? []))),
        ];

        $formJson = json_encode($formPayload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $prompt = <<<TXT
Você é um redator brasileiro de currículos profissionais. O candidato
preencheu o formulário abaixo. Sua tarefa: POLIR e ORGANIZAR os textos,
não inventar dados novos.

{$roleLine}

FORMULÁRIO DO CANDIDATO (JSON):
{$formJson}

INSTRUÇÕES OBRIGATÓRIAS:

1. Devolva JSON estrito, SEM texto antes ou depois, EXATAMENTE neste schema:
{
  "ats_score": 75,
  "suggestions": ["dica 1", "dica 2", "dica 3"],
  "resume": {
    "contact": { "full_name":"", "role_title":"", "email":"", "phone":"", "address":"", "linkedin":"", "portfolio":"", "birth_date":"" },
    "objective": "2-3 linhas focadas no cargo alvo",
    "summary": "parágrafo curto com anos de experiência e diferenciais",
    "experiences": [
      { "role":"", "company":"", "period":"", "bullets":["b1", "b2", "b3"] }
    ],
    "education":      [ { "degree":"", "institution":"", "period":"" } ],
    "skills":         { "hard":[], "soft":[] },
    "languages":      [ { "language":"", "level":"" } ],
    "certifications": [ { "name":"", "issuer":"", "year":"" } ]
  }
}

2. CONTACT: copie EXATAMENTE do formulário. Não altere nada.

3. OBJECTIVE (objetivo profissional):
   - Se "user_objective" foi preenchido, POLIR o texto (correção, fluência).
   - Se estiver vazio, GERAR 2-3 linhas focadas no "target_role" usando
     as skills e experiências informadas.

4. SUMMARY (resumo profissional):
   - Se "user_summary" foi preenchido, POLIR.
   - Se estiver vazio, GERAR parágrafo de 4-6 linhas com base nas
     experiências, formação e skills declaradas.

5. EXPERIENCES: para cada item vindo do formulário, GERE de 2 a 4 bullets
   profissionais a partir do "raw_activities":
   - Verbo no passado ("Coordenou", "Reduziu", "Implantou").
   - Se o candidato mencionou número/resultado no raw_activities, mantenha.
   - Se NÃO mencionou, descreva a atividade sem inventar número específico.
   - Máximo 25 palavras cada bullet.
   - Copie "role", "company", "period" IDÊNTICOS ao formulário.
   - Se "raw_activities" estiver vazio, devolva "bullets": [].

6. EDUCATION, SKILLS, LANGUAGES, CERTIFICATIONS: copie do formulário.
   Pode padronizar capitalização (ex: "excel" → "Excel avançado" se souber).

7. REGRA CRÍTICA — SEM INVENÇÕES:
   - NUNCA use "[preencher: ...]" ou colchetes.
   - Se um campo veio vazio, mantenha vazio "" (string) ou [] (array).
   - Não invente empresas, cursos, datas.

8. FORMATO:
   - Sem markdown (# ## ** *) em nenhum campo.
   - Sem "Aqui está o currículo..." ou meta-texto.
   - Texto puro em português brasileiro.

9. "ats_score": inteiro 0-100 = quão bem esse CV passaria em filtros ATS.

10. "suggestions": 3-5 dicas OBJETIVAS pro candidato melhorar
    (ex: "Adicionar 2 conquistas quantificadas", "Incluir certificação recente").
TXT;

        $raw = $this->chat([
            ['role' => 'user', 'content' => $prompt],
        ]);

        $decoded = $this->safeJsonDecode($raw);

        // Normaliza e valida a estrutura do "resume"
        $resume = $this->normalizeResumeStructure($decoded['resume'] ?? []);

        // Se a IA deixou algum campo vazio, aplica fallback do formulário
        $this->fillResumeFallback($resume, $formPayload);

        // Sanitiza UTF-8 das suggestions
        $suggestions = array_values((array) ($decoded['suggestions'] ?? []));
        $suggestions = array_map(
            fn ($s) => is_string($s) ? mb_scrub((string) $s, 'UTF-8') : '',
            $suggestions
        );
        $suggestions = array_values(array_filter($suggestions, fn ($s) => $s !== ''));

        return [
            'ats_score'      => (int) ($decoded['ats_score'] ?? 0),
            'suggestions'    => $suggestions,
            'resume'         => $resume,
            'final_markdown' => $this->resumeToPlainText($resume),
        ];
    }

    /**
     * Garante que campos vazios na resposta da IA usem o dado bruto do formulário.
     * Segurança extra caso a Groq devolva JSON incompleto.
     */
    private function fillResumeFallback(array &$resume, array $form): void
    {
        // Contact: se algum campo veio vazio, copia do formulário
        foreach ($resume['contact'] as $k => $v) {
            if ($v === '' && ! empty($form['contact'][$k])) {
                $resume['contact'][$k] = $form['contact'][$k];
            }
        }

        // Se não veio nenhuma experience mas o form tinha, copia do form
        if (empty($resume['experiences']) && ! empty($form['experiences'])) {
            $resume['experiences'] = array_map(fn ($e) => [
                'role'    => $e['role'] ?? '',
                'company' => $e['company'] ?? '',
                'period'  => $e['period'] ?? '',
                'bullets' => array_filter(array_map('trim', explode("\n", $e['raw_activities'] ?? ''))),
            ], $form['experiences']);
        }

        if (empty($resume['education']) && ! empty($form['education'])) {
            $resume['education'] = $form['education'];
        }
        if (empty($resume['skills']['hard']) && ! empty($form['skills']['hard'])) {
            $resume['skills']['hard'] = $form['skills']['hard'];
        }
        if (empty($resume['skills']['soft']) && ! empty($form['skills']['soft'])) {
            $resume['skills']['soft'] = $form['skills']['soft'];
        }
        if (empty($resume['languages']) && ! empty($form['languages'])) {
            $resume['languages'] = $form['languages'];
        }
        if (empty($resume['certifications']) && ! empty($form['certifications'])) {
            $resume['certifications'] = $form['certifications'];
        }
    }

    /**
     * Normaliza o objeto resume vindo da IA — garante todos os campos existem
     * como arrays/strings e sanitiza UTF-8 em todos os textos.
     *
     * @return array{objective:string,summary:string,experiences:array,education:array,skills:array,certifications:array,languages:array}
     */
    private function normalizeResumeStructure(mixed $raw): array
    {
        $r = is_array($raw) ? $raw : [];

        $scrub = fn ($v) => is_string($v) ? mb_scrub($v, 'UTF-8') : '';

        // Contato — dados extraídos do CV pela IA
        $contactRaw = (array) ($r['contact'] ?? []);
        $contact = [
            'full_name'  => $scrub($contactRaw['full_name']  ?? ''),
            'role_title' => $scrub($contactRaw['role_title'] ?? ''),
            'email'      => $scrub($contactRaw['email']      ?? ''),
            'phone'      => $scrub($contactRaw['phone']      ?? ''),
            'address'    => $scrub($contactRaw['address']    ?? ''),
            'linkedin'   => $scrub($contactRaw['linkedin']   ?? ''),
            'portfolio'  => $scrub($contactRaw['portfolio']  ?? ''),
            'birth_date' => $scrub($contactRaw['birth_date'] ?? ''),
        ];

        $experiences = [];
        foreach ((array) ($r['experiences'] ?? []) as $e) {
            if (! is_array($e)) continue;
            $bullets = array_values(array_filter(array_map(
                $scrub,
                (array) ($e['bullets'] ?? [])
            ), fn ($b) => $b !== ''));
            $experiences[] = [
                'role'    => $scrub($e['role'] ?? ''),
                'company' => $scrub($e['company'] ?? ''),
                'period'  => $scrub($e['period'] ?? ''),
                'bullets' => $bullets,
            ];
        }

        $education = [];
        foreach ((array) ($r['education'] ?? []) as $ed) {
            if (! is_array($ed)) continue;
            $education[] = [
                'degree'      => $scrub($ed['degree'] ?? ''),
                'institution' => $scrub($ed['institution'] ?? ''),
                'period'      => $scrub($ed['period'] ?? ''),
            ];
        }

        $skillsRaw = (array) ($r['skills'] ?? []);
        $skills = [
            'hard' => array_values(array_filter(array_map($scrub, (array) ($skillsRaw['hard'] ?? [])), fn ($s) => $s !== '')),
            'soft' => array_values(array_filter(array_map($scrub, (array) ($skillsRaw['soft'] ?? [])), fn ($s) => $s !== '')),
        ];

        $certifications = [];
        foreach ((array) ($r['certifications'] ?? []) as $c) {
            if (! is_array($c)) continue;
            $certifications[] = [
                'name'   => $scrub($c['name'] ?? ''),
                'issuer' => $scrub($c['issuer'] ?? ''),
                'year'   => $scrub($c['year'] ?? ''),
            ];
        }

        $languages = [];
        foreach ((array) ($r['languages'] ?? []) as $l) {
            if (! is_array($l)) continue;
            $languages[] = [
                'language' => $scrub($l['language'] ?? ''),
                'level'    => $scrub($l['level'] ?? ''),
            ];
        }

        return [
            'contact'        => $contact,
            'objective'      => $scrub($r['objective'] ?? ''),
            'summary'        => $scrub($r['summary'] ?? ''),
            'experiences'    => $experiences,
            'education'      => $education,
            'skills'         => $skills,
            'certifications' => $certifications,
            'languages'      => $languages,
        ];
    }

    /**
     * Converte a estrutura do resume em texto plano formatado — para exibir
     * no card de preview simples, permitir copiar, e para fallback em locais
     * antigos que esperavam $resumeResult['final_markdown'].
     */
    private function resumeToPlainText(array $r): string
    {
        $out = [];

        if (! empty($r['objective'])) {
            $out[] = "OBJETIVO PROFISSIONAL";
            $out[] = $r['objective'];
            $out[] = '';
        }
        if (! empty($r['summary'])) {
            $out[] = "RESUMO PROFISSIONAL";
            $out[] = $r['summary'];
            $out[] = '';
        }
        if (! empty($r['experiences'])) {
            $out[] = "EXPERIÊNCIA PROFISSIONAL";
            foreach ($r['experiences'] as $e) {
                $head = trim(implode(' · ', array_filter([$e['role'], $e['company'], $e['period']])));
                if ($head !== '') $out[] = $head;
                foreach ($e['bullets'] ?? [] as $b) {
                    $out[] = "• {$b}";
                }
                $out[] = '';
            }
        }
        if (! empty($r['education'])) {
            $out[] = "FORMAÇÃO ACADÊMICA";
            foreach ($r['education'] as $ed) {
                $line = trim(implode(' · ', array_filter([$ed['degree'], $ed['institution'], $ed['period']])));
                if ($line !== '') $out[] = $line;
            }
            $out[] = '';
        }
        $hard = $r['skills']['hard'] ?? [];
        $soft = $r['skills']['soft'] ?? [];
        if ($hard || $soft) {
            $out[] = "HABILIDADES";
            if ($hard) $out[] = "Hard skills: " . implode(', ', $hard);
            if ($soft) $out[] = "Soft skills: " . implode(', ', $soft);
            $out[] = '';
        }
        if (! empty($r['certifications'])) {
            $out[] = "CERTIFICAÇÕES";
            foreach ($r['certifications'] as $c) {
                $out[] = trim(implode(' · ', array_filter([$c['name'], $c['issuer'], $c['year']])));
            }
            $out[] = '';
        }
        if (! empty($r['languages'])) {
            $out[] = "IDIOMAS";
            foreach ($r['languages'] as $l) {
                $out[] = trim(implode(' — ', array_filter([$l['language'], $l['level']])));
            }
        }

        return mb_scrub(trim(implode("\n", $out)), 'UTF-8');
    }

    /**
     * Monta um bloco de contexto detalhado com TUDO que temos do perfil do
     * candidato (nome, contato, bio, experiências, formação, skills, idiomas).
     * A IA usa isso para preencher lacunas do CV colado sem precisar inventar.
     */
    private function buildProfileContext(?User $user): string
    {
        if (! $user) return '';

        $lines = ["\n\nCONTEXTO DO PERFIL DO CANDIDATO (usar para preencher informações faltantes):"];

        // Identificação
        if ($user->name)     $lines[] = "- Nome: {$user->name}";
        if ($user->headline) $lines[] = "- Cargo/Headline: {$user->headline}";
        if ($user->email)    $lines[] = "- Email: {$user->email}";

        $profile = $user->candidateProfile;
        if ($profile) {
            if ($profile->phone)    $lines[] = "- Telefone: {$profile->phone}";
            if ($profile->city)     $lines[] = "- Cidade: {$profile->city}";
            if ($profile->state)    $lines[] = "- Estado: {$profile->state}";
            if ($profile->linkedin_url) $lines[] = "- LinkedIn: {$profile->linkedin_url}";
            if ($profile->portfolio_url) $lines[] = "- Portfólio: {$profile->portfolio_url}";
            if ($profile->bio)      $lines[] = "- Bio: " . trim(preg_replace('/\s+/', ' ', (string) $profile->bio));

            // Experiências
            $exps = $profile->experiences()->latest('start_date')->limit(10)->get();
            if ($exps->isNotEmpty()) {
                $lines[] = "\nEXPERIÊNCIAS DO PERFIL:";
                foreach ($exps as $e) {
                    $ini = $e->start_date ? $e->start_date->format('m/Y') : '?';
                    $fim = $e->current ? 'atual' : ($e->end_date ? $e->end_date->format('m/Y') : '?');
                    $line = "• {$e->role} · {$e->company_name} · {$ini} – {$fim}";
                    if (! empty($e->description)) {
                        $desc = trim(preg_replace('/\s+/', ' ', (string) $e->description));
                        $line .= "\n  Descrição: {$desc}";
                    }
                    $lines[] = $line;
                }
            }

            // Formação
            $edus = $profile->educations()->latest('end_date')->limit(6)->get();
            if ($edus->isNotEmpty()) {
                $lines[] = "\nFORMAÇÃO DO PERFIL:";
                foreach ($edus as $ed) {
                    $ini = $ed->start_date ? $ed->start_date->format('Y') : '?';
                    $fim = $ed->end_date ? $ed->end_date->format('Y') : '?';
                    $lines[] = "• {$ed->degree} · {$ed->institution} · {$ini} – {$fim}";
                }
            }

            // Skills
            $skills = $profile->skills()->pluck('name')->all();
            if (! empty($skills)) {
                $lines[] = "\nSKILLS DECLARADAS: " . implode(', ', $skills);
            }
        }

        return count($lines) > 1 ? implode("\n", $lines) : '';
    }

    /**
     * Remove marcações markdown que estragam o layout do PDF gerado (dompdf
     * não renderiza markdown). Também tira "análise do currículo" caso a IA
     * inclua no final_markdown por descuido.
     *
     * IMPORTANTE: sanitiza UTF-8 no final, pois a Groq às vezes devolve
     * sequências UTF-8 quebradas (parciais/inválidas) — se não sanitizamos,
     * json_encode() retorna FALSE e o Livewire quebra o snapshot com
     * "undefined is not valid JSON" no browser.
     */
    private function sanitizeResumeMarkdown(string $text): string
    {
        // Garante UTF-8 válido antes de qualquer regex — regex em UTF-8
        // inválido pode gerar mais caracteres corrompidos.
        if (! mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        // Substitui sequências UTF-8 malformadas por ? (não perde muito)
        $text = mb_scrub($text, 'UTF-8');

        // TODOS os regex abaixo usam o modificador /u — em regex sem /u,
        // `.+?` pode cortar bytes multi-byte no meio (·, –, á, ç etc)
        // e corromper o UTF-8 novamente.

        // Remove headings markdown (#, ##, ###)
        $text = (string) preg_replace('/^#{1,6}\s+/mu', '', $text);
        // Remove negrito **texto** → texto
        $text = (string) preg_replace('/\*\*(.+?)\*\*/u', '$1', $text);
        // Remove itálico *texto*
        $text = (string) preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/u', '$1', $text);

        // Remove placeholders no formato [preencher: ...] ou [Nome da...]
        $lines = explode("\n", $text);
        $cleaned = [];
        foreach ($lines as $line) {
            // Se a linha é APENAS um bullet com [placeholder], descarta
            if (preg_match('/^\s*[•\-*]\s*\[[^\]]+\]\s*$/u', $line)) continue;
            // Remove trechos [preencher: X] deixando o resto da linha
            $line = (string) preg_replace('/\s*\[[Pp]reencher[^\]]*\]\s*/u', '', $line);
            // Remove outros brackets curtos vazios que sobraram: [Ano], [%]
            $line = (string) preg_replace('/\s*\[[^\]]{1,25}\]\s*/u', ' ', $line);
            // Remove "· –" ou "· ·" que sobraram quando placeholder foi apagado
            $line = (string) preg_replace('/·\s*[–\-]\s*·/u', '·', $line);
            $line = (string) preg_replace('/·\s*·/u', '·', $line);
            // NÃO uso trim() com chars multi-byte pq trim() é byte-based.
            // Uso preg_replace pra remover chars não desejados nas pontas.
            $line = (string) preg_replace('/^[\s\t·\-–]+|[\s\t·\-–]+$/u', '', $line);
            $cleaned[] = $line;
        }
        $text = implode("\n", $cleaned);

        // Remove blocos de análise/comentário que a IA às vezes insere
        $badSections = [
            '/^##?\s*An[aá]lise do Curr[ií]culo.*$/miu',
            '/^##?\s*Pontos Fortes.*$/miu',
            '/^##?\s*Oportunidades de Melhoria.*$/miu',
            '/^##?\s*Recomenda[çc][õo]es.*$/miu',
            '/^Perfil Profissional\s*$/miu',
        ];
        foreach ($badSections as $pat) {
            $text = (string) preg_replace($pat, '', $text);
        }
        // Normaliza espaços em excesso
        $text = (string) preg_replace("/\n{3,}/u", "\n\n", $text);
        // Sanitiza UTF-8 uma vez mais no final (defesa em profundidade)
        $text = mb_scrub($text, 'UTF-8');
        return trim($text);
    }

    /**
     * Gera uma carta de apresentação personalizada.
     * O usuário informa o cargo livre + descrição opcional (não precisa ser vaga
     * cadastrada na plataforma — pode ser qualquer empresa externa).
     */
    public function generateCoverLetter(User $user, string $jobTitle, string $jobDescription = ''): string
    {
        $userName = $user->name ?? $user->username ?? 'candidato(a)';
        $userHead = $user->headline ?? 'profissional';
        $bio      = $user->candidateProfile?->bio ?? '';
        $skills   = $user->candidateProfile?->skills()->pluck('name')->all() ?? [];

        $descLine = trim($jobDescription) !== ''
            ? "Descrição da vaga:\n\"\"\"\n{$jobDescription}\n\"\"\""
            : "Descrição da vaga: não informada — infira o que faz mais sentido para esse cargo.";

        $prompt = <<<TXT
Escreva uma carta de apresentação em português brasileiro, com no máximo 220 palavras,
para o(a) candidato(a) {$userName} ({$userHead}) se candidatar à vaga "{$jobTitle}".
Foque em resultados concretos e alinhamento cultural. Comece com uma saudação
profissional (Prezado(a) Recrutador(a)) e termine com uma linha convidando à
próxima etapa. Não use clichês vazios.

Contexto sobre o candidato:
- Cargo/headline: {$userHead}
- Skills declaradas: {$this->list($skills)}
- Bio: {$bio}

{$descLine}
TXT;

        return $this->chat([
            ['role' => 'user', 'content' => $prompt],
        ]);
    }

    /**
     * Analisa o "match" entre usuário e vaga.
     * Aceita cargo livre + descrição opcional (não precisa ser vaga cadastrada).
     *
     * @return array{match:int, strengths:array<int,string>, gaps:array<int,string>, advice:string}
     */
    public function analyzeJobMatch(User $user, string $jobTitle, string $jobDescription = ''): array
    {
        $skills = $user->candidateProfile?->skills()->pluck('name')->all() ?? [];
        $bio    = $user->candidateProfile?->bio ?? '';
        $descLine = trim($jobDescription) !== ''
            ? "Descrição da vaga:\n\"\"\"\n{$jobDescription}\n\"\"\""
            : "Descrição da vaga: não informada (infira o que faz sentido).";

        $prompt = <<<TXT
Você é um recrutador experiente. Avalie o alinhamento do candidato à vaga.
Retorne JSON estrito no formato:
{"match": <inteiro 0-100>, "strengths": ["ponto 1", "ponto 2"], "gaps": ["gap 1", "gap 2"], "advice": "conselho acionável"}

Candidato:
- Headline: {$user->headline}
- Bio: {$bio}
- Skills: {$this->list($skills)}

Vaga:
- Título: {$jobTitle}
- {$descLine}
TXT;

        $raw = $this->chat([['role' => 'user', 'content' => $prompt]]);
        $data = $this->safeJsonDecode($raw);

        return [
            'match'     => (int) ($data['match'] ?? 0),
            'strengths' => array_values((array) ($data['strengths'] ?? [])),
            'gaps'      => array_values((array) ($data['gaps'] ?? [])),
            'advice'    => (string) ($data['advice'] ?? ''),
        ];
    }

    /**
     * Sugere vagas ao usuário via IA.
     *
     * @return array<int, array{job_id:int, match:int, reason:string}>
     */
    public function suggestJobs(User $user, int $limit = 10): array
    {
        // Busca vagas abertas para servir como candidatas
        $jobs = JobListing::query()
            ->where('status', 'open')
            ->latest('published_at')
            ->limit(30)
            ->get(['id', 'title', 'seniority', 'modality']);

        if ($jobs->isEmpty()) {
            return [];
        }

        $payload = $jobs->map(fn ($j) => [
            'id'        => $j->id,
            'title'     => $j->title,
            'seniority' => $j->seniority,
            'modality'  => $j->modality,
        ])->all();

        $skills = $user->candidateProfile?->skills()->pluck('name')->all() ?? [];

        $prompt = 'Selecione até ' . $limit . ' vagas mais aderentes ao perfil e retorne '
            . 'JSON no formato: [{"job_id":123,"match":85,"reason":"..."}]'
            . "\nPerfil: " . json_encode(['headline' => $user->headline, 'skills' => $skills], JSON_UNESCAPED_UNICODE)
            . "\nVagas: " . json_encode($payload, JSON_UNESCAPED_UNICODE);

        $raw  = $this->chat([['role' => 'user', 'content' => $prompt]]);
        $data = $this->safeJsonDecode($raw);

        if (! is_array($data)) {
            return [];
        }

        return array_slice(array_map(fn ($row) => [
            'job_id' => (int) ($row['job_id'] ?? 0),
            'match'  => (int) ($row['match']  ?? 0),
            'reason' => (string) ($row['reason'] ?? ''),
        ], $data), 0, $limit);
    }

    /**
     * Otimiza perfil do LinkedIn — gera headline + seção "Sobre".
     *
     * @return array{headline:string, about:string, keywords:array<int,string>}
     */
    public function optimizeLinkedIn(User $user, ?string $targetRole = null): array
    {
        $bio     = $user->candidateProfile?->bio ?? '';
        $head    = $user->headline ?? '';
        $skills  = $user->candidateProfile?->skills()->pluck('name')->all() ?? [];
        $roleLine = $targetRole ? "Vaga alvo: {$targetRole}" : "Vaga alvo: não informada";

        $prompt = <<<TXT
Você é um consultor especialista em LinkedIn para profissionais brasileiros.
Gere um headline e um texto "Sobre" OTIMIZADOS para atrair recrutadores.

{$roleLine}
Headline atual: {$head}
Bio atual: {$bio}
Skills: {$this->list($skills)}

REGRAS:
- Headline: máximo 220 caracteres, formato "Cargo | Diferencial | Palavra-chave"
- Sobre: 3-4 parágrafos, 800-1200 chars, com bullets, começando com um gancho
- Use palavras-chave da área que recrutadores buscam
- Tom profissional mas humano, em primeira pessoa

Retorne JSON estrito:
{"headline":"...","about":"...","keywords":["palavra1","palavra2","palavra3","palavra4","palavra5"]}
TXT;

        $raw = $this->chat([['role' => 'user', 'content' => $prompt]]);
        $data = $this->safeJsonDecode($raw);

        return [
            'headline' => (string) ($data['headline'] ?? ''),
            'about'    => (string) ($data['about'] ?? ''),
            'keywords' => array_values(array_map('strval', $data['keywords'] ?? [])),
        ];
    }

    /**
     * Gera 5 perguntas prováveis para uma entrevista.
     * O usuário informa o CARGO livre + uma DESCRIÇÃO opcional da vaga
     * (pode ser entrevista externa em qualquer empresa, não precisa estar cadastrada).
     *
     * @return array<int,array{question:string,tip:string,category:string}>
     */
    public function simulateInterview(User $user, string $jobTitle, string $jobDescription = ''): array
    {
        $skills = $user->candidateProfile?->skills()->pluck('name')->all() ?? [];
        $descLine = $jobDescription !== ''
            ? "DESCRIÇÃO/CONTEXTO: {$jobDescription}"
            : "DESCRIÇÃO: (não informada — infira o que faz mais sentido para esse cargo)";

        $prompt = <<<TXT
Você é um recrutador experiente. Gere 5 perguntas prováveis para uma entrevista
do cargo abaixo. Misture: 2 comportamentais (STAR), 2 técnicas específicas da área,
1 sobre motivação/cultura.

CARGO: {$jobTitle}
{$descLine}
CANDIDATO: {$user->name} | Skills declaradas: {$this->list($skills)}

Para cada pergunta, dê uma DICA curta (60-120 chars) de como o candidato deve responder.
Priorize perguntas realistas que recrutadores brasileiros fazem hoje.

Retorne JSON estrito:
{"questions":[{"question":"...","tip":"...","category":"comportamental|tecnica|cultura"}]}
TXT;

        $raw  = $this->chat([['role' => 'user', 'content' => $prompt]]);
        $data = $this->safeJsonDecode($raw);

        $questions = $data['questions'] ?? [];
        if (! is_array($questions)) return [];

        return array_values(array_map(fn ($q) => [
            'question' => (string) ($q['question'] ?? ''),
            'tip'      => (string) ($q['tip'] ?? ''),
            'category' => (string) ($q['category'] ?? 'geral'),
        ], array_slice($questions, 0, 5)));
    }

    /**
     * Sugere uma resposta profissional para um e-mail recebido.
     *
     * @return array{reply:string, tone:string, tips:array<int,string>}
     */
    public function replyEmail(string $incomingEmail, string $intention = 'aceitar'): array
    {
        $intentionMap = [
            'aceitar' => 'aceitar a proposta/pedido de forma cordial',
            'recusar' => 'recusar de forma profissional e educada',
            'negociar'=> 'negociar termos (salário, prazo, escopo) mantendo a boa relação',
            'esclarecer' => 'pedir esclarecimentos e mais informações',
            'agradecer'  => 'agradecer sem se comprometer ainda',
        ];
        $goal = $intentionMap[$intention] ?? $intentionMap['aceitar'];

        $prompt = <<<TXT
Você é um coach de comunicação corporativa. Gere uma resposta profissional em
português brasileiro para o e-mail abaixo. Objetivo: {$goal}.

REGRAS:
- Comece com saudação apropriada (Prezado(a), Olá)
- Máximo 4 parágrafos, tom cordial e claro
- Termine com fechamento profissional + assinatura genérica
- Dê 3 dicas curtas de como personalizar

E-MAIL RECEBIDO:
"""
{$incomingEmail}
"""

Retorne JSON estrito:
{"reply":"texto completo da resposta","tone":"formal|cordial|assertivo","tips":["dica 1","dica 2","dica 3"]}
TXT;

        $raw  = $this->chat([['role' => 'user', 'content' => $prompt]]);
        $data = $this->safeJsonDecode($raw);

        return [
            'reply' => (string) ($data['reply'] ?? ''),
            'tone'  => (string) ($data['tone'] ?? 'cordial'),
            'tips'  => array_values(array_map('strval', $data['tips'] ?? [])),
        ];
    }

    /**
     * Gera um plano de carreira personalizado com base no perfil do usuário.
     *
     * @return array{
     *   diagnosis:string,
     *   next_steps:array<int,array{title:string,description:string,priority:string}>,
     *   skills_to_learn:array<int,string>,
     *   estimated_timeline:string
     * }
     */
    public function careerPlan(User $user, ?string $goalRole = null): array
    {
        $bio     = $user->candidateProfile?->bio ?? '';
        $skills  = $user->candidateProfile?->skills()->pluck('name')->all() ?? [];
        $exp     = $user->candidateProfile?->experiences()->latest('start_date')->limit(3)->get()
            ?->map(fn ($e) => $e->role . ' @ ' . $e->company_name)?->all() ?? [];
        $edu     = $user->candidateProfile?->educations()->latest('end_date')->limit(3)->get()
            ?->map(fn ($e) => $e->degree . ' - ' . $e->institution)?->all() ?? [];
        $goalLine = $goalRole ? "OBJETIVO: {$goalRole}" : "OBJETIVO: não informado (sugira 1-2 caminhos)";

        $prompt = <<<TXT
Você é um coach de carreira sênior. Baseado no perfil abaixo, monte um plano de
desenvolvimento profissional REALISTA e ACIONÁVEL para 6-12 meses.

{$goalLine}
Cargo atual/Headline: {$user->headline}
Bio: {$bio}
Skills: {$this->list($skills)}
Últimas experiências: {$this->list($exp)}
Formação: {$this->list($edu)}

Retorne JSON estrito:
{
  "diagnosis": "2-3 frases avaliando ponto atual + oportunidades",
  "next_steps": [
    {"title":"Passo 1","description":"o que fazer (com detalhes)","priority":"alta|media|baixa"},
    {"title":"Passo 2","description":"...","priority":"..."},
    {"title":"Passo 3","description":"...","priority":"..."},
    {"title":"Passo 4","description":"...","priority":"..."},
    {"title":"Passo 5","description":"...","priority":"..."}
  ],
  "skills_to_learn": ["skill 1","skill 2","skill 3","skill 4","skill 5"],
  "estimated_timeline": "Em X meses você pode chegar em Y..."
}
TXT;

        $raw  = $this->chat([['role' => 'user', 'content' => $prompt]]);
        $data = $this->safeJsonDecode($raw);

        return [
            'diagnosis'         => (string) ($data['diagnosis'] ?? ''),
            'next_steps'        => array_values(array_map(fn ($s) => [
                'title'       => (string) ($s['title'] ?? ''),
                'description' => (string) ($s['description'] ?? ''),
                'priority'    => (string) ($s['priority'] ?? 'media'),
            ], is_array($data['next_steps'] ?? null) ? $data['next_steps'] : [])),
            'skills_to_learn'   => array_values(array_map('strval', $data['skills_to_learn'] ?? [])),
            'estimated_timeline'=> (string) ($data['estimated_timeline'] ?? ''),
        ];
    }

    /** Helper: transforma array em string separada por vírgulas (ou "nenhum(a)" se vazio). */
    private function list(array $items): string
    {
        return empty($items) ? 'nenhum(a) informado(a)' : implode(', ', $items);
    }

    /**
     * Chat completion genérico — sempre prefixa o system prompt de segurança.
     *
     * @param array<int, array{role:string, content:string}> $messages
     * @param array<int, array<string,mixed>> $tools
     */
    public function chat(array $messages, array $tools = []): string
    {
        $systemGuard = (string) config('ai.system_prompt_guard', '');

        // Injeta o system prompt no topo se ainda não estiver presente
        array_unshift($messages, [
            'role'    => 'system',
            'content' => $systemGuard,
        ]);

        $params = [
            'model'       => config('ai.model'),
            'messages'    => $messages,
            'max_tokens'  => (int) config('ai.max_response_tokens', 2000),
            'temperature' => 0.7,
        ];

        if (! empty($tools)) {
            $params['tools'] = $tools;
        }

        try {
            $response = $this->client->chat()->create($params);
            $choice   = $response->choices[0] ?? null;

            return (string) ($choice?->message?->content ?? '');
        } catch (Throwable $e) {
            report($e);

            return '';
        }
    }

    /**
     * Tenta decodificar JSON mesmo quando o modelo retorna com cerca ```json.
     *
     * @return array<mixed>
     */
    private function safeJsonDecode(string $raw): array
    {
        $clean = trim($raw);
        $clean = (string) preg_replace('/^```(?:json)?/i', '', $clean);
        $clean = (string) preg_replace('/```$/', '', $clean);
        $clean = trim($clean);

        $decoded = json_decode($clean, true);

        return is_array($decoded) ? $decoded : [];
    }
}
