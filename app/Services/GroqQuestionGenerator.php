<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SkillAssessment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gera questões dinâmicas para os testes usando a API da Groq
 * (compatível com OpenAI). Cada chamada devolve 20 questões novas
 * e únicas — o candidato nunca vê a mesma pergunta duas vezes.
 *
 * Fallback: se a Groq falhar ou retornar JSON inválido, tenta usar
 * as questões armazenadas no banco (backup) via {@see SkillAssessment::questions}.
 */
class GroqQuestionGenerator
{
    public function __construct()
    {
    }

    /**
     * Gera 20 questões para um SkillAssessment usando a Groq.
     *
     * Retorna array indexado com objetos anônimos no formato:
     *   [{ id, statement, options[], correct_index, explanation }]
     *
     * Os IDs são temporários (negativos, únicos por chamada) — usados
     * apenas para navegação na UI. NÃO são salvos no banco.
     */
    public function generate(SkillAssessment $assessment, int $count = 20): array
    {
        $key      = (string) config('services.groq.key');
        $model    = (string) config('services.groq.model');
        $endpoint = (string) config('services.groq.endpoint');

        if ($key === '') {
            Log::warning('[GroqGenerator] GROQ_API_KEY não configurada — usando fallback do banco');
            return $this->fallbackFromDb($assessment, $count);
        }

        $prompt = $this->buildPrompt($assessment, $count);

        try {
            $response = Http::withToken($key)
                ->timeout(90)
                ->connectTimeout(15)
                ->acceptJson()
                ->asJson()
                ->post($endpoint, [
                    'model'                 => $model,
                    'temperature'           => 0.9, // alta para variar bem
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
                Log::error('[GroqGenerator] HTTP ' . $response->status(), [
                    'body' => substr($response->body(), 0, 800),
                ]);
                return $this->fallbackFromDb($assessment, $count);
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            if ($content === '') {
                Log::error('[GroqGenerator] Content vazio na resposta', ['data' => $data]);
                return $this->fallbackFromDb($assessment, $count);
            }

            // Remove eventual markdown ```json ... ```
            $content = preg_replace('/^```(?:json)?\s*|\s*```\s*$/mi', '', trim($content));

            $payload = json_decode($content, true);
            if (! is_array($payload) || empty($payload['questions'])) {
                Log::error('[GroqGenerator] JSON inválido', ['content' => substr($content, 0, 500)]);
                return $this->fallbackFromDb($assessment, $count);
            }

            $questions = $this->normalize($payload['questions'], $count);
            if (count($questions) < $count) {
                Log::warning('[GroqGenerator] IA retornou menos que ' . $count . ' questões (' . count($questions) . '), completando com banco');
                $needed  = $count - count($questions);
                $backup  = $this->fallbackFromDb($assessment, $needed);
                $questions = array_merge($questions, $backup);
            }

            return array_slice($questions, 0, $count);
        } catch (\Throwable $e) {
            Log::error('[GroqGenerator] Exception: ' . $e->getMessage());
            return $this->fallbackFromDb($assessment, $count);
        }
    }

    /**
     * Prompt otimizado para produzir questões de alto nível.
     */
    private function buildPrompt(SkillAssessment $assessment, int $count): string
    {
        $tema      = $assessment->title;
        $categoria = $assessment->category;
        $nivel     = $assessment->difficultyLabel();
        $desc      = $assessment->description ?: $assessment->short_description;

        return <<<PROMPT
Gere um teste de proficiência DE ALTO NÍVEL sobre:

TEMA: {$tema}
CATEGORIA: {$categoria}
NÍVEL: {$nivel}
CONTEXTO: {$desc}

REGRAS OBRIGATÓRIAS:

1. CENÁRIO OBRIGATÓRIO: cada questão começa com um mini-caso real de 2-4 frases (200-350 caracteres) descrevendo situação profissional brasileira específica, com:
   - Nome fictício de pessoa/empresa (ex: "Ana, analista da fintech PagaFácil", "Loja Reis & Cia em Curitiba")
   - Contexto com números concretos (valores em R$, prazos, quantidades)
   - Depois a PERGUNTA objetiva sobre o cenário

2. FORMATO STATEMENT: use "CENÁRIO: [caso]. PERGUNTA: [pergunta]." — entre 250 e 500 caracteres total.

3. OPÇÕES ELABORADAS: cada uma das 4 opções deve ter 40-150 caracteres, descrever uma AÇÃO ou ANÁLISE plausível. Nada de "todas as anteriores" ou opções de 1 palavra.

4. DISTRATORES INTELIGENTES: as 3 opções erradas devem ser: (a) quase certa que pega descuidado, (b) confunde com conceito parecido, (c) parece técnica mas está errada.

5. EXPLICAÇÃO PROFUNDA (200-400 chars): explique por que a certa está certa E por que 1 distrator confunde.

6. PROGRESSÃO: questões 1-6 aplicação direta, 7-14 cenários com análise, 15-20 casos complexos.

7. NÃO REPITA cenários entre questões — varie nomes, empresas, cidades, valores.

8. Retorne EXATAMENTE {$count} questões.

FORMATO JSON (sem markdown, sem ```):

{
  "questions": [
    {
      "statement": "CENÁRIO: ... PERGUNTA: ...",
      "options": ["opção A elaborada", "opção B", "opção C", "opção D"],
      "correct_index": 2,
      "explanation": "Por que a certa é correta e por que UM dos distratores confunde"
    }
  ]
}

IMPORTANTE — DISTRIBUIÇÃO DA RESPOSTA CORRETA:
Varie o `correct_index` de forma equilibrada entre 0, 1, 2 e 3 ao longo das {$count} questões.
NÃO coloque a resposta correta sempre em `correct_index: 0` (opção A).
Distribua aproximadamente 25% para cada índice (0, 1, 2, 3).

Gere agora.
PROMPT;
    }

    /**
     * Valida e normaliza cada questão retornada pela IA.
     *
     * @return array<int,object>
     */
    private function normalize(array $questions, int $max): array
    {
        $result = [];
        $tempId = -1;

        foreach ($questions as $q) {
            if (! isset($q['statement'], $q['options'], $q['correct_index'])) {
                continue;
            }
            if (! is_array($q['options']) || count($q['options']) !== 4) {
                continue;
            }
            $ci = (int) $q['correct_index'];
            if ($ci < 0 || $ci > 3) continue;

            $options = array_values(array_map('strval', $q['options']));

            // Embaralha as opções e recalcula o correct_index — garantia de que
            // a resposta correta não fica sempre em "A" mesmo se a IA insistir.
            $correctText = $options[$ci];
            shuffle($options);
            $ci = (int) array_search($correctText, $options, true);

            $result[] = (object) [
                'id'            => $tempId--, // negativo temporário
                'statement'     => (string) $q['statement'],
                'options'       => $options,
                'correct_index' => $ci,
                'explanation'   => (string) ($q['explanation'] ?? ''),
            ];

            if (count($result) >= $max) break;
        }

        return $result;
    }

    /**
     * Fallback: usa as questões estáticas do banco (se existirem)
     * quando a Groq falhar. Se o banco também estiver vazio, retorna [].
     */
    public function fallbackFromDb(SkillAssessment $assessment, int $count): array
    {
        $questions = $assessment->questions()
            ->inRandomOrder()
            ->limit($count)
            ->get()
            ->map(fn ($q) => (object) [
                'id'            => $q->id,          // ID real (positivo)
                'statement'     => $q->statement,
                'options'       => is_array($q->options) ? $q->options : (array) json_decode($q->options ?? '[]', true),
                'correct_index' => (int) $q->correct_index,
                'explanation'   => (string) $q->explanation,
            ])
            ->all();

        return $questions;
    }
}
