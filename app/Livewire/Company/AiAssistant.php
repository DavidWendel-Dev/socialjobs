<?php

declare(strict_types=1);

namespace App\Livewire\Company;

use App\Models\Application;
use App\Models\JobListing;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Assistente IA — RH (empresa).
 *
 * Ferramentas para acelerar o ciclo de contratação:
 *  - job-description     : Melhorar descrição de vaga
 *  - interview-questions : Gerar perguntas de entrevista
 *  - cv-match            : Analisar CV do candidato vs vaga
 *  - feedback-email      : Redigir e-mail de feedback
 *  - salary              : Sugerir salário de mercado
 *  - insights            : Insights automáticos das vagas
 */
#[Layout('layouts.app')]
#[Title('Assistente IA (empresa) · SocialJobs')]
class AiAssistant extends Component
{
    public string $tab = 'job-description';

    // === Aba 1: melhorar descrição de vaga ===
    public string $jdTitle = '';
    public string $jdCurrent = '';
    public string $jdSkills = '';
    public string $outputJobDescription = '';

    // === Aba 2: perguntas de entrevista ===
    public ?int   $iqJobId = null;
    public string $iqLevel = 'pleno';
    public int    $iqCount = 8;
    public string $outputInterviewQuestions = '';

    // === Aba 3: match CV × vaga ===
    public ?int   $cmJobId = null;
    public ?int   $cmCandidateId = null;
    public string $outputCvMatch = '';
    public ?int   $cmScore = null;

    // === Aba 4: e-mail de feedback ===
    public string $fbType = 'approval';
    public ?int   $fbCandidateId = null;
    public string $fbTone = 'friendly';
    public string $outputFeedbackEmail = '';

    // === Aba 5: salário de mercado ===
    public string $slRole = '';
    public string $slLevel = 'pleno';
    public string $slContract = 'CLT';
    public string $slRegion = '';
    public string $outputSalary = '';

    // === Aba 6: insights ===
    public string $outputInsights = '';

    public function setTab(string $t): void
    {
        $allowed = ['job-description', 'interview-questions', 'cv-match', 'feedback-email', 'salary', 'insights'];
        $this->tab = in_array($t, $allowed, true) ? $t : 'job-description';
    }

    public function clear(string $which): void
    {
        match ($which) {
            'job-description'     => $this->outputJobDescription = '',
            'interview-questions' => $this->outputInterviewQuestions = '',
            'cv-match'            => $this->resetCvMatch(),
            'feedback-email'      => $this->outputFeedbackEmail = '',
            'salary'              => $this->outputSalary = '',
            'insights'            => $this->outputInsights = '',
            default               => null,
        };
    }

    private function resetCvMatch(): void
    {
        $this->outputCvMatch = '';
        $this->cmScore = null;
    }

    /* ============================================================
     |  IA — wrapper Groq
     * ============================================================ */
    private function callGroq(string $system, string $user, float $temp = 0.7): string
    {
        $key = (string) config('services.groq.key');
        if ($key === '') {
            return "⚠️ A chave da API Groq não está configurada. Configure `GROQ_API_KEY` no `.env`.";
        }

        $model = (string) (config('services.groq.model') ?: 'llama-3.3-70b-versatile');
        $endpoint = (string) (config('services.groq.endpoint') ?: 'https://api.groq.com/openai/v1/chat/completions');

        try {
            $resp = Http::withToken($key)
                ->timeout(90)
                ->post($endpoint, [
                    'model'       => $model,
                    'temperature' => $temp,
                    'messages'    => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user',   'content' => $user],
                    ],
                ]);

            if (! $resp->ok()) {
                return "⚠️ A IA respondeu com erro (HTTP " . $resp->status() . "). Tente novamente em instantes.";
            }

            $content = (string) data_get($resp->json(), 'choices.0.message.content', '');
            return $content !== '' ? $content : "⚠️ A IA não retornou conteúdo. Tente novamente.";
        } catch (\Throwable $e) {
            return "⚠️ Não foi possível conectar à IA agora. Detalhe: " . $e->getMessage();
        }
    }

    private function companyProfileId(): ?int
    {
        return auth()->user()?->companyProfile?->id;
    }

    private function companyName(): string
    {
        $cp = auth()->user()?->companyProfile;
        return $cp?->trade_name ?: ($cp?->legal_name ?: (auth()->user()->name ?? 'Sua empresa'));
    }

    /* ============================================================
     |  Ações por aba
     * ============================================================ */

    public function runJobDescription(): void
    {
        $this->validate([
            'jdTitle' => 'required|string|min:3|max:200',
        ]);

        $sys = "Você é um copywriter especialista em vagas de emprego brasileiras. "
             . "Devolva SEMPRE em Markdown com seções: Sobre a empresa, O que você vai fazer, "
             . "Requisitos, Diferenciais, Benefícios. Tom profissional, direto, inclusivo. "
             . "Use bullet points em Requisitos, Diferenciais e Benefícios.";

        $user = "Empresa: {$this->companyName()}\n"
              . "Cargo: {$this->jdTitle}\n"
              . ($this->jdSkills !== '' ? "Skills chave: {$this->jdSkills}\n" : '')
              . ($this->jdCurrent !== '' ? "Rascunho atual do gestor:\n{$this->jdCurrent}\n" : '')
              . "\nGere uma descrição de vaga completa e atraente.";

        $this->outputJobDescription = $this->callGroq($sys, $user, 0.7);
    }

    public function runInterviewQuestions(): void
    {
        $this->validate([
            'iqJobId' => 'required|integer',
            'iqLevel' => 'required|string',
            'iqCount' => 'required|integer|min:5|max:15',
        ]);

        $job = JobListing::where('company_profile_id', $this->companyProfileId())
            ->where('id', $this->iqJobId)->firstOrFail();

        $sys = "Você é um recrutador experiente. Gere perguntas de entrevista em Markdown. "
             . "Cada pergunta deve ter: **Pergunta**, *Objetivo* (o que avalia) e *Sinais de alerta*.";

        $user = "Vaga: {$job->title}\n"
              . "Nível: {$this->iqLevel}\n"
              . "Descrição: " . mb_substr((string) $job->description, 0, 800) . "\n\n"
              . "Gere {$this->iqCount} perguntas.";

        $this->outputInterviewQuestions = $this->callGroq($sys, $user);
    }

    public function runCvMatch(): void
    {
        $this->validate([
            'cmJobId'       => 'required|integer',
            'cmCandidateId' => 'required|integer',
        ]);

        $job = JobListing::where('company_profile_id', $this->companyProfileId())
            ->where('id', $this->cmJobId)->firstOrFail();

        $app = Application::whereHas('jobListing', fn ($q) => $q->where('company_profile_id', $this->companyProfileId()))
            ->where('user_id', $this->cmCandidateId)
            ->with(['user.candidateProfile.skills', 'user.candidateProfile.experiences'])
            ->firstOrFail();

        $candidate = $app->user;
        $cp = $candidate->candidateProfile;

        $skills = ($cp && $cp->skills->isNotEmpty()) ? $cp->skills->pluck('name')->implode(', ') : 'não informado';
        $exps = ($cp && $cp->experiences->isNotEmpty())
            ? $cp->experiences->map(fn ($e) => "- {$e->role} em {$e->company_name}")->implode("\n")
            : 'não informado';

        $sys = "Você é um recrutador sênior. Analise a compatibilidade do candidato com a vaga. "
             . "Devolva em Markdown começando com **SCORE: XX/100** na primeira linha. "
             . "Depois: **Pontos fortes**, **Gaps**, **Red flags**, **Recomendação** "
             . "(contratar / entrevistar / rejeitar) com justificativa curta.";

        $user = "VAGA: {$job->title}\n"
              . "Descrição: " . mb_substr((string) $job->description, 0, 700) . "\n\n"
              . "CANDIDATO: {$candidate->name}\n"
              . "Headline: " . ($candidate->headline ?? '—') . "\n"
              . "Bio: " . mb_substr((string) ($cp?->bio ?? ''), 0, 400) . "\n"
              . "Skills: {$skills}\n"
              . "Experiências:\n{$exps}\n\n"
              . "Analise objetivamente.";

        $out = $this->callGroq($sys, $user, 0.4);
        $this->outputCvMatch = $out;

        if (preg_match('/SCORE:\s*(\d+)/i', $out, $m)) {
            $this->cmScore = min(100, (int) $m[1]);
        } else {
            $this->cmScore = null;
        }
    }

    public function runFeedbackEmail(): void
    {
        $this->validate([
            'fbCandidateId' => 'required|integer',
        ]);

        $app = Application::whereHas('jobListing', fn ($q) => $q->where('company_profile_id', $this->companyProfileId()))
            ->where('user_id', $this->fbCandidateId)
            ->with(['user', 'jobListing'])
            ->firstOrFail();

        $tipos = [
            'approval'   => 'aprovação final / oferta de emprego',
            'next-step'  => 'convite para próxima etapa (teste técnico ou entrevista)',
            'interview'  => 'agendar entrevista',
            'rejection'  => 'rejeição respeitosa',
        ];
        $tipo = $tipos[$this->fbType] ?? $tipos['approval'];
        $tom = $this->fbTone === 'formal' ? 'formal e institucional' : 'amigável e humano';

        $sys = "Você é um recrutador brasileiro escrevendo um e-mail profissional. "
             . "Devolva no formato: **Assunto:** <linha>\\n\\n<corpo do e-mail>. Sem placeholders bobos.";

        $user = "Empresa: {$this->companyName()}\n"
              . "Candidato: {$app->user->name}\n"
              . "Vaga: {$app->jobListing->title}\n"
              . "Tipo do e-mail: {$tipo}\n"
              . "Tom: {$tom}\n\n"
              . "Escreva o e-mail.";

        $this->outputFeedbackEmail = $this->callGroq($sys, $user, 0.7);
    }

    public function runSalary(): void
    {
        $this->validate([
            'slRole'   => 'required|string|min:3',
            'slLevel'  => 'required|string',
        ]);

        $sys = "Você é um consultor de RH sênior com conhecimento do mercado brasileiro. "
             . "Devolva em Markdown: **Faixa estimada** (em BRL mensal), **Benefícios comuns** (bullets), "
             . "**Observações do mercado atual**. Seja realista, sem chutar.";

        $user = "Cargo: {$this->slRole}\n"
              . "Nível: {$this->slLevel}\n"
              . "Contrato: {$this->slContract}\n"
              . "Região: " . ($this->slRegion ?: 'Brasil') . "\n\n"
              . "Sugira faixa salarial de mercado.";

        $this->outputSalary = $this->callGroq($sys, $user, 0.4);
    }

    public function runInsights(): void
    {
        $cpId = $this->companyProfileId();
        if (! $cpId) {
            $this->outputInsights = "⚠️ Sem CompanyProfile.";
            return;
        }

        $jobs = JobListing::where('company_profile_id', $cpId)
            ->withCount([
                'applications',
                'applications as interviews_count' => fn ($q) => $q->where('status', 'interview'),
                'applications as hires_count'      => fn ($q) => $q->where('status', 'hired'),
                'applications as rejects_count'    => fn ($q) => $q->where('status', 'rejected'),
            ])
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        if ($jobs->isEmpty()) {
            $this->outputInsights = "Você ainda não publicou nenhuma vaga. Publique a primeira e volte aqui!";
            return;
        }

        $tabela = "| Vaga | Status | Aplicaram | Entrevistas | Contratados | Rejeitados | Dias aberta |\n"
                . "|------|--------|-----------|-------------|-------------|------------|-------------|\n";
        foreach ($jobs as $j) {
            $dias = $j->published_at ? (int) abs($j->published_at->diffInDays(now())) : 0;
            $tabela .= "| {$j->title} | {$j->status} | {$j->applications_count} | {$j->interviews_count} | {$j->hires_count} | {$j->rejects_count} | {$dias} |\n";
        }

        $sys = "Você é um analista de RH. Analise as métricas das vagas e devolva em Markdown: "
             . "**Situação geral**, **Vagas com problemas** (nomear e sugerir ações), "
             . "**Oportunidades**, **Próximos passos** (3 bullets). Seja específico.";

        $user = "Empresa: {$this->companyName()}\n\nMétricas:\n{$tabela}";
        $this->outputInsights = $this->callGroq($sys, $user, 0.5);
    }

    /* ============================================================
     |  Dados para dropdowns
     * ============================================================ */

    public function render(): View
    {
        $cpId = $this->companyProfileId();

        $openJobs = $cpId
            ? JobListing::where('company_profile_id', $cpId)->where('status', 'open')->orderBy('title')->get(['id','title'])
            : collect();

        // Candidatos para dropdown do match (aplicantes da vaga selecionada)
        $cmCandidates = ($cpId && $this->cmJobId)
            ? Application::where('job_listing_id', $this->cmJobId)
                ->whereHas('jobListing', fn ($q) => $q->where('company_profile_id', $cpId))
                ->with('user:id,name,username')
                ->get()
                ->pluck('user')
                ->filter()
                ->unique('id')
                ->values()
            : collect();

        // Candidatos para dropdown do e-mail — todos que já aplicaram em qualquer vaga
        $fbCandidates = $cpId
            ? Application::whereHas('jobListing', fn ($q) => $q->where('company_profile_id', $cpId))
                ->with('user:id,name,username')
                ->get()
                ->pluck('user')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values()
            : collect();

        return view('livewire.company.ai-assistant', [
            'openJobs'     => $openJobs,
            'cmCandidates' => $cmCandidates,
            'fbCandidates' => $fbCandidates,
        ]);
    }
}
