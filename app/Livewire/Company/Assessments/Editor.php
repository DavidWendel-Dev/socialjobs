<?php

declare(strict_types=1);

namespace App\Livewire\Company\Assessments;

use App\Models\JobListing;
use App\Models\SkillAssessment;
use App\Models\SkillAssessmentQuestion;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Editor de teste · SocialJobs')]
class Editor extends Component
{
    public ?int $assessmentId = null;

    /* -------------------------- metadados -------------------------- */
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string|max:60')]
    public string $category = 'Geral';

    #[Validate('required|string|max:500')]
    public string $short_description = '';

    #[Validate('nullable|string|max:5000')]
    public string $description = '';

    #[Validate('required|in:basic,intermediate,advanced')]
    public string $difficulty = 'intermediate';

    #[Validate('required|integer|min:5|max:180')]
    public int $duration_minutes = 30;

    #[Validate('required|integer|min:0|max:100')]
    public int $passing_score = 60;

    #[Validate('required|in:public,invite_only')]
    public string $visibility = 'invite_only';

    #[Validate('nullable|integer|exists:job_listings,id')]
    public ?int $job_listing_id = null;

    /** Contexto/tema para injetar no prompt da IA (não persiste). */
    public string $focus_topic = '';

    /**
     * Questões em memória.
     * Cada item: ['statement' => '', 'options' => ['','','',''], 'correct_index' => 0, 'explanation' => '']
     *
     * @var array<int, array{statement:string, options:array<int,string>, correct_index:int, explanation:string}>
     */
    public array $questions = [];

    /** UI: estado de geração pela IA. */
    public bool $generating = false;

    public function mount(?SkillAssessment $assessment = null): void
    {
        $cp = auth()->user()?->companyProfile;
        abort_unless($cp !== null, 403);

        // Modo edição
        if ($assessment && $assessment->exists) {
            abort_unless(
                $assessment->owner_type === 'company'
                    && (int) $assessment->company_profile_id === (int) $cp->id,
                403
            );

            $this->assessmentId      = $assessment->id;
            $this->title             = (string) $assessment->title;
            $this->category          = (string) $assessment->category;
            $this->short_description = (string) $assessment->short_description;
            $this->description       = (string) ($assessment->description ?? '');
            $this->difficulty        = (string) $assessment->difficulty;
            $this->duration_minutes  = (int) $assessment->duration_minutes;
            $this->passing_score     = (int) $assessment->passing_score;
            $this->visibility        = (string) ($assessment->visibility ?? 'invite_only');
            $this->job_listing_id    = $assessment->job_listing_id;

            $this->questions = $assessment->questions()
                ->orderBy('position')
                ->get()
                ->map(fn (SkillAssessmentQuestion $q) => [
                    'statement'     => (string) $q->statement,
                    'options'       => is_array($q->options)
                        ? array_values(array_map('strval', $q->options))
                        : ['', '', '', ''],
                    'correct_index' => (int) $q->correct_index,
                    'explanation'   => (string) ($q->explanation ?? ''),
                ])
                ->all();
        }
    }

    /* ================================================================
       Manipulação de questões
       ================================================================ */

    public function addQuestion(): void
    {
        $this->questions[] = [
            'statement'     => '',
            'options'       => ['', '', '', ''],
            'correct_index' => 0,
            'explanation'   => '',
        ];
    }

    public function removeQuestion(int $index): void
    {
        if (isset($this->questions[$index])) {
            unset($this->questions[$index]);
            $this->questions = array_values($this->questions);
        }
    }

    /* ================================================================
       Geração via IA (Groq) — 10 questões customizadas com base no
       título/categoria/dificuldade + focus_topic informado pela empresa.
       ================================================================ */
    public function generateWithAi(): void
    {
        $this->validate([
            'title'    => 'required|string|max:255',
            'category' => 'required|string|max:60',
        ]);

        $this->generating = true;

        $questions = $this->callGroq(10);

        if (empty($questions)) {
            session()->flash('error', 'Não foi possível gerar as questões agora. Tente novamente em instantes ou adicione manualmente.');
            $this->generating = false;
            return;
        }

        // Anexa às existentes (não substitui — a empresa pode iterar)
        $this->questions = array_merge($this->questions, $questions);
        $this->generating = false;

        session()->flash('status', count($questions) . ' questões geradas pela IA. Revise antes de salvar.');
    }

    /**
     * @return array<int, array{statement:string, options:array<int,string>, correct_index:int, explanation:string}>
     */
    private function callGroq(int $count): array
    {
        $key      = (string) config('services.groq.key');
        $model    = (string) config('services.groq.model');
        $endpoint = (string) config('services.groq.endpoint');

        if ($key === '') {
            Log::warning('[CompanyAssessmentEditor] GROQ_API_KEY não configurada.');
            return [];
        }

        $prompt = $this->buildPrompt($count);

        try {
            $response = Http::withToken($key)
                ->timeout(90)
                ->connectTimeout(15)
                ->acceptJson()
                ->asJson()
                ->post($endpoint, [
                    'model'                 => $model,
                    'temperature'           => 0.9,
                    'max_completion_tokens' => 8192,
                    'top_p'                 => 1,
                    'stream'                => false,
                    'response_format'       => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role'    => 'system',
                            'content' => 'Você é um examinador sênior de banca profissional brasileira (padrão FGV/CESPE). Gera questões elaboradas com cenários reais, dados concretos e distratores plausíveis. Retorna SEMPRE JSON válido, sem markdown.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('[CompanyAssessmentEditor] Groq HTTP ' . $response->status(), [
                    'body' => substr($response->body(), 0, 800),
                ]);
                return [];
            }

            $data    = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            if ($content === '') {
                return [];
            }

            $content = preg_replace('/^```(?:json)?\s*|\s*```\s*$/mi', '', trim($content));
            $payload = json_decode($content, true);

            if (! is_array($payload) || empty($payload['questions'])) {
                return [];
            }

            $out = [];
            foreach ($payload['questions'] as $q) {
                if (! isset($q['statement'], $q['options'], $q['correct_index'])) {
                    continue;
                }
                if (! is_array($q['options']) || count($q['options']) !== 4) {
                    continue;
                }
                $ci = (int) $q['correct_index'];
                if ($ci < 0 || $ci > 3) {
                    continue;
                }

                $out[] = [
                    'statement'     => (string) $q['statement'],
                    'options'       => array_values(array_map('strval', $q['options'])),
                    'correct_index' => $ci,
                    'explanation'   => (string) ($q['explanation'] ?? ''),
                ];

                if (count($out) >= $count) {
                    break;
                }
            }

            return $out;
        } catch (\Throwable $e) {
            Log::error('[CompanyAssessmentEditor] Groq exception: ' . $e->getMessage());
            return [];
        }
    }

    private function buildPrompt(int $count): string
    {
        $tema      = $this->title;
        $categoria = $this->category;
        $nivel     = match ($this->difficulty) {
            'basic'    => 'Básico',
            'advanced' => 'Avançado',
            default    => 'Intermediário',
        };
        $desc  = $this->short_description ?: $this->description;
        $foco  = $this->focus_topic ?: 'não especificado — use o próprio tema como norte.';

        return <<<PROMPT
Gere um teste de proficiência CUSTOMIZADO de alto nível sobre:

TEMA: {$tema}
CATEGORIA: {$categoria}
NÍVEL: {$nivel}
CONTEXTO/BREVE DESCRIÇÃO: {$desc}
FOCO ESPECIAL DESTA EMPRESA: {$foco}

REGRAS OBRIGATÓRIAS:

1. CENÁRIO OBRIGATÓRIO em cada questão: comece com um mini-caso real (2-4 frases)
   com nome fictício de pessoa/empresa BRASILEIRA, cidade, números concretos.
   Depois a PERGUNTA objetiva.

2. FORMATO STATEMENT: "CENÁRIO: [caso]. PERGUNTA: [pergunta]." — 250-500 caracteres.

3. 4 opções por questão, cada uma com 40-150 caracteres. Nada de "todas anteriores".

4. Distratores inteligentes: quase certos, confusão com conceito parecido, distrator técnico.

5. EXPLICAÇÃO (200-400 chars): por que a certa é correta e por que UM distrator confunde.

6. Ajuste dificuldade ao NÍVEL informado.

7. USE o FOCO ESPECIAL — ancore os cenários no contexto da empresa.

8. Retorne EXATAMENTE {$count} questões.

FORMATO JSON (sem markdown):

{
  "questions": [
    {
      "statement": "CENÁRIO: ... PERGUNTA: ...",
      "options": ["A", "B", "C", "D"],
      "correct_index": 0,
      "explanation": "..."
    }
  ]
}

Gere agora.
PROMPT;
    }

    /* ================================================================
       Persistência
       ================================================================ */
    public function save(): void
    {
        $this->validate();

        $cp = auth()->user()?->companyProfile;
        abort_unless($cp !== null, 403);

        // Ao menos 5 questões válidas
        $valid = collect($this->questions)->filter(function ($q) {
            return is_string($q['statement'] ?? null)
                && trim($q['statement']) !== ''
                && is_array($q['options'] ?? null)
                && count($q['options']) === 4
                && collect($q['options'])->every(fn ($o) => is_string($o) && trim($o) !== '')
                && isset($q['correct_index'])
                && $q['correct_index'] >= 0
                && $q['correct_index'] <= 3;
        })->values();

        if ($valid->count() < 5) {
            $this->addError('questions', 'Adicione ao menos 5 questões válidas (com enunciado, 4 opções preenchidas e a correta marcada).');
            return;
        }

        // Slug: baseado no título + id da empresa (para não colidir com plataforma)
        $baseSlug = Str::slug($this->title);
        $slug     = $baseSlug . '-c' . $cp->id;

        // Se colidir (edição), mantém o existente; se criação, gera único
        if ($this->assessmentId) {
            $assessment = SkillAssessment::query()
                ->where('id', $this->assessmentId)
                ->where('company_profile_id', $cp->id)
                ->firstOrFail();
        } else {
            // garante slug único
            $i = 1;
            while (SkillAssessment::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-c' . $cp->id . '-' . $i++;
            }
            $assessment = new SkillAssessment();
            $assessment->slug               = $slug;
            $assessment->owner_type         = 'company';
            $assessment->company_profile_id = $cp->id;
            $assessment->created_by_user_id = auth()->id();
            $assessment->is_active          = true;
            $assessment->icon               = 'academic';
            $assessment->color              = 'brand';
            $assessment->xp_reward          = 100;
        }

        DB::transaction(function () use ($assessment, $valid) {
            $assessment->title             = $this->title;
            $assessment->category          = $this->category;
            $assessment->short_description = $this->short_description;
            $assessment->description       = $this->description ?: null;
            $assessment->difficulty        = $this->difficulty;
            $assessment->duration_minutes  = $this->duration_minutes;
            $assessment->passing_score     = $this->passing_score;
            $assessment->visibility        = $this->visibility;
            $assessment->job_listing_id    = $this->job_listing_id ?: null;
            $assessment->save();

            // Reescreve questões (mais simples que diffar)
            $assessment->questions()->delete();
            foreach ($valid as $i => $q) {
                SkillAssessmentQuestion::create([
                    'skill_assessment_id' => $assessment->id,
                    'statement'           => trim($q['statement']),
                    'options'             => array_values(array_map('trim', $q['options'])),
                    'correct_index'       => (int) $q['correct_index'],
                    'explanation'         => trim((string) ($q['explanation'] ?? '')) ?: null,
                    'position'            => $i,
                ]);
            }
        });

        session()->flash('status', $this->assessmentId ? 'Teste atualizado!' : 'Teste criado! Agora envie convites aos candidatos.');
        $this->redirectRoute('company.assessments.results', ['assessment' => $assessment->id], navigate: true);
    }

    /* ================================================================
       Render
       ================================================================ */
    public function render(): View
    {
        $cp = auth()->user()?->companyProfile;

        $jobs = $cp
            ? JobListing::query()
                ->where('company_profile_id', $cp->id)
                ->orderByDesc('id')
                ->limit(50)
                ->get(['id', 'title'])
            : collect();

        return view('livewire.company.assessments.editor', [
            'jobs' => $jobs,
        ]);
    }
}
