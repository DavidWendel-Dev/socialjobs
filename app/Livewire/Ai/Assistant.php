<?php

declare(strict_types=1);

namespace App\Livewire\Ai;

use App\Models\JobListing;
use App\Services\AiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Assistente IA — 8 ferramentas de carreira alimentadas por IA (Groq).
 *
 * Abas:
 *  - resume    : Melhorar currículo
 *  - cover     : Carta de apresentação
 *  - analyze   : Analisar compatibilidade com vaga
 *  - suggest   : Sugerir vagas com match
 *  - linkedin  : Otimizar perfil LinkedIn (headline + sobre)
 *  - interview : Simulador de entrevista (5 perguntas prováveis)
 *  - email     : Responder e-mail profissional
 *  - career    : Plano de carreira personalizado
 */
#[Layout('layouts.app')]
#[Title('Assistente IA · SocialJobs')]
class Assistant extends Component
{
    public string $tab = 'resume';

    // ===== Aba: currículo (gerador guiado com IA) =====
    /**
     * Estrutura de contato do usuário (nome, email, endereço etc).
     * Preenchida pelo usuário no formulário — NÃO tenta puxar do perfil
     * automaticamente. Cada campo tem tipo string e default vazio.
     */
    public array $resumeContact = [
        'full_name'  => '',
        'role_title' => '',
        'email'      => '',
        'phone'      => '',
        'address'    => '',
        'linkedin'   => '',
        'portfolio'  => '',
        'birth_date' => '',
    ];
    /** Vaga alvo para o CV. Obrigatório. */
    public string $resumeTarget = '';
    /** Texto livre do candidato pro objetivo. IA pode polir ou gerar do zero se vazio. */
    public string $resumeObjective = '';
    /** Texto livre do candidato pro resumo profissional. */
    public string $resumeSummary = '';
    /** Lista repetível de experiências (o candidato pode adicionar/remover). */
    public array $resumeExperiences = [];
    /** Lista repetível de formações. */
    public array $resumeEducations = [];
    /** Skills técnicas separadas por vírgula (será split em array). */
    public string $resumeHardSkills = '';
    /** Skills comportamentais. */
    public string $resumeSoftSkills = '';
    /** Idiomas repetíveis. */
    public array $resumeLanguages = [];
    /** Certificações repetíveis. */
    public array $resumeCertifications = [];

    /**
     * Opção de foto: 'profile' (usa foto de perfil), 'upload' (foto dedicada) ou 'none'.
     * A foto de upload é salva em storage temporariamente e caminho vai no PDF.
     */
    public string $resumePhotoOption = 'none';
    /** Caminho da foto uploaded (relativo a storage/app). */
    public string $resumePhotoPath = '';

    /** Template ativo do PDF: 'classic' | 'modern' | 'creative'. */
    public string $resumeTemplate = 'classic';
    public array  $resumeResult = [];

    // ===== Aba: carta =====
    // Usuário digita livremente o cargo + descrição da vaga.
    // A vaga NÃO precisa estar cadastrada na plataforma.
    public string $coverJobTitle = '';
    public string $coverJobDescription = '';
    public string $coverResult = '';

    // ===== Aba: analisar vaga =====
    // Usuário digita livremente o cargo + descrição da vaga.
    public string $analyzeJobTitle = '';
    public string $analyzeJobDescription = '';
    public array  $analyzeResult = [];

    // ===== Aba: sugestões =====
    public array  $suggestions = [];

    // ===== Aba: LinkedIn =====
    public string $linkedinTarget = '';
    public array  $linkedinResult = [];

    // ===== Aba: simulador de entrevista =====
    // O usuário digita livremente o cargo/vaga (não precisa estar cadastrada
    // na plataforma — pode ser entrevista externa em outra empresa).
    public string $interviewJobTitle = '';
    public string $interviewJobDescription = '';
    public array  $interviewResult = [];

    // ===== Aba: responder e-mail =====
    public string $emailIncoming = '';
    public string $emailIntention = 'aceitar';
    public array  $emailResult = [];

    // ===== Aba: plano de carreira =====
    public string $careerGoal = '';
    public array  $careerResult = [];

    // ===== Estado global =====
    public bool $loading = false;
    public string $errorMessage = '';

    /** Abas válidas — protege contra tab arbitrário via URL. */
    private const VALID_TABS = ['resume','cover','analyze','suggest','linkedin','interview','email','career'];

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, self::VALID_TABS, true) ? $tab : 'resume';
        $this->errorMessage = '';
    }

    // ============================================================
    //  Ações
    // ============================================================

    // ============================================================
    //  Ações — Gerador de currículo guiado
    // ============================================================

    /** Adiciona uma nova experiência vazia ao formulário. */
    public function addExperience(): void
    {
        $this->resumeExperiences[] = [
            'role'           => '',
            'company'        => '',
            'period'         => '',
            'raw_activities' => '',
        ];
    }

    /** Remove uma experiência pelo índice. */
    public function removeExperience(int $index): void
    {
        if (isset($this->resumeExperiences[$index])) {
            unset($this->resumeExperiences[$index]);
            $this->resumeExperiences = array_values($this->resumeExperiences);
        }
    }

    public function addEducation(): void
    {
        $this->resumeEducations[] = ['degree' => '', 'institution' => '', 'period' => ''];
    }

    public function removeEducation(int $index): void
    {
        if (isset($this->resumeEducations[$index])) {
            unset($this->resumeEducations[$index]);
            $this->resumeEducations = array_values($this->resumeEducations);
        }
    }

    public function addLanguage(): void
    {
        $this->resumeLanguages[] = ['language' => '', 'level' => ''];
    }

    public function removeLanguage(int $index): void
    {
        if (isset($this->resumeLanguages[$index])) {
            unset($this->resumeLanguages[$index]);
            $this->resumeLanguages = array_values($this->resumeLanguages);
        }
    }

    public function addCertification(): void
    {
        $this->resumeCertifications[] = ['name' => '', 'issuer' => '', 'year' => ''];
    }

    public function removeCertification(int $index): void
    {
        if (isset($this->resumeCertifications[$index])) {
            unset($this->resumeCertifications[$index]);
            $this->resumeCertifications = array_values($this->resumeCertifications);
        }
    }

    /** Define qual foto usar no CV: 'profile', 'upload' ou 'none'. */
    public function setPhotoOption(string $opt): void
    {
        $this->resumePhotoOption = in_array($opt, ['profile', 'upload', 'none'], true) ? $opt : 'none';
        if ($opt !== 'upload') {
            $this->resumePhotoPath = '';
        }
    }

    public function setResumeTemplate(string $tpl): void
    {
        $this->resumeTemplate = in_array($tpl, ['classic','modern','creative'], true)
            ? $tpl
            : 'classic';
    }

    /**
     * Gera e devolve o PDF do CV melhorado para download.
     * O template é escolhido pelo estado $resumeTemplate.
     *
     * IMPORTANTE: no Livewire 3, ao retornar download precisamos usar
     * response()->streamDownload() — retornar Response direto quebra o
     * snapshot JSON e gera "undefined is not valid JSON" no front.
     */
    public function downloadResumePdf()
    {
        if (empty($this->resumeResult['resume']) && empty($this->resumeResult['final_markdown'])) {
            $this->errorMessage = 'Gere o currículo melhorado primeiro.';
            return;
        }

        $data = $this->buildResumePdfData();

        $tpl = match ($this->resumeTemplate) {
            'modern'   => 'pdf.resume-modern',
            'creative' => 'pdf.resume-creative',
            default    => 'pdf.resume-classic',
        };

        $slug = \Illuminate\Support\Str::slug($data['name'] ?: 'curriculo');
        $filename = "curriculo-{$slug}.pdf";

        $pdfOutput = Pdf::loadView($tpl, $data)->setPaper('a4', 'portrait')->output();

        return response()->streamDownload(function () use ($pdfOutput) {
            echo $pdfOutput;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Monta o array de dados que os templates PDF esperam.
     * Cabeçalho vem do form (contact do usuário) + foto conforme opção.
     */
    private function buildResumePdfData(): array
    {
        $u = auth()->user();
        $resume = $this->resumeResult['resume'] ?? [];
        $c = $resume['contact'] ?? $this->resumeContact;

        // Resolve foto conforme opção do usuário
        $photoDataUri = '';
        if ($this->resumePhotoOption === 'profile' && $u?->avatar_url) {
            $photoDataUri = $this->imagePathToDataUri($u->avatar_url);
        } elseif ($this->resumePhotoOption === 'upload' && $this->resumePhotoPath !== '') {
            $full = storage_path('app/' . ltrim($this->resumePhotoPath, '/'));
            if (is_file($full)) {
                $photoDataUri = $this->imagePathToDataUri($full);
            }
        }

        return [
            // Cabeçalho — copia direto do contact vindo da IA (que já foi
            // preenchido a partir do formulário do usuário)
            'name'          => $c['full_name']  ?? '',
            'headline'      => $c['role_title'] ?? '',
            'email'         => $c['email']      ?? '',
            'phone'         => $c['phone']      ?? '',
            'city'          => '',
            'state'         => '',
            'address'       => $c['address']    ?? '',
            'linkedin_url'  => $c['linkedin']   ?? '',
            'portfolio_url' => $c['portfolio']  ?? '',
            'birth_date'    => $c['birth_date'] ?? '',
            'photo'         => $photoDataUri,

            // Corpo do CV vindo da IA
            'resume' => $resume ?: [
                'objective' => '', 'summary' => '', 'experiences' => [],
                'education' => [], 'skills' => ['hard' => [], 'soft' => []],
                'certifications' => [], 'languages' => [],
            ],
        ];
    }

    /**
     * Converte um caminho de imagem (local ou URL absoluta) em data URI base64
     * pra embutir no PDF (dompdf tem restrições com URL http externa).
     */
    private function imagePathToDataUri(string $pathOrUrl): string
    {
        try {
            // URL absoluta pro storage — converte pro path local
            if (str_starts_with($pathOrUrl, 'http')) {
                $storageUrl = rtrim(config('app.url'), '/') . '/storage/';
                if (str_starts_with($pathOrUrl, $storageUrl)) {
                    $rel = substr($pathOrUrl, strlen($storageUrl));
                    $pathOrUrl = storage_path('app/public/' . $rel);
                }
            }
            if (! is_file($pathOrUrl)) return '';

            $data = @file_get_contents($pathOrUrl);
            if ($data === false) return '';

            $mime = @mime_content_type($pathOrUrl) ?: 'image/jpeg';
            return 'data:' . $mime . ';base64,' . base64_encode($data);
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function generateResume(): void
    {
        $this->errorMessage = '';
        $this->resumeResult = [];

        if (trim($this->resumeTarget) === '') {
            $this->errorMessage = 'Informe a vaga alvo antes de gerar o currículo.';
            return;
        }
        if (trim($this->resumeContact['full_name'] ?? '') === '') {
            $this->errorMessage = 'Informe seu nome completo.';
            return;
        }

        $this->loading = true;

        try {
            $hard = array_values(array_filter(array_map('trim', explode(',', $this->resumeHardSkills))));
            $soft = array_values(array_filter(array_map('trim', explode(',', $this->resumeSoftSkills))));

            $form = [
                'target_role'    => $this->resumeTarget,
                'contact'        => $this->resumeContact,
                'user_objective' => $this->resumeObjective,
                'user_summary'   => $this->resumeSummary,
                'experiences'    => $this->resumeExperiences,
                'education'      => $this->resumeEducations,
                'skills'         => ['hard' => $hard, 'soft' => $soft],
                'languages'      => $this->resumeLanguages,
                'certifications' => $this->resumeCertifications,
            ];

            $this->resumeResult = app(AiService::class)->generateResume($form);

            if (empty($this->resumeResult['resume'])) {
                $this->errorMessage = 'A IA não retornou resposta. Tente novamente em instantes.';
            }
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Erro ao chamar a IA: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function generateCover(): void
    {
        $this->errorMessage = '';
        $this->coverResult = '';

        if (trim($this->coverJobTitle) === '') {
            $this->errorMessage = 'Informe o cargo/vaga para a carta.';
            return;
        }

        $this->loading = true;

        try {
            $this->coverResult = app(AiService::class)
                ->generateCoverLetter(
                    auth()->user(),
                    trim($this->coverJobTitle),
                    trim($this->coverJobDescription)
                );
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Erro ao chamar a IA: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function analyzeJob(): void
    {
        $this->errorMessage = '';
        $this->analyzeResult = [];

        if (trim($this->analyzeJobTitle) === '') {
            $this->errorMessage = 'Informe o cargo/vaga a analisar.';
            return;
        }

        $this->loading = true;

        try {
            $this->analyzeResult = app(AiService::class)
                ->analyzeJobMatch(
                    auth()->user(),
                    trim($this->analyzeJobTitle),
                    trim($this->analyzeJobDescription)
                );
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Erro ao chamar a IA: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function suggest(): void
    {
        $this->errorMessage = '';
        $this->suggestions = [];

        $this->loading = true;

        try {
            $raw = app(AiService::class)->suggestJobs(auth()->user(), 10);

            if (empty($raw)) {
                $this->errorMessage = 'A IA não retornou sugestões. Verifique se há vagas abertas.';
                return;
            }

            $ids  = array_map(fn ($r) => $r['job_id'], $raw);
            $jobs = JobListing::query()
                ->with('companyProfile')
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            foreach ($raw as $row) {
                $job = $jobs[$row['job_id']] ?? null;
                if ($job) {
                    $this->suggestions[] = [
                        'job'    => $job,
                        'match'  => (int) $row['match'],
                        'reason' => (string) $row['reason'],
                    ];
                }
            }
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Erro ao chamar a IA: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function optimizeLinkedIn(): void
    {
        $this->errorMessage = '';
        $this->linkedinResult = [];
        $this->loading = true;

        try {
            $this->linkedinResult = app(AiService::class)
                ->optimizeLinkedIn(auth()->user(), $this->linkedinTarget ?: null);
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Erro ao chamar a IA: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function simulateInterview(): void
    {
        $this->errorMessage = '';
        $this->interviewResult = [];

        if (trim($this->interviewJobTitle) === '') {
            $this->errorMessage = 'Informe o cargo/vaga da entrevista.';
            return;
        }

        $this->loading = true;

        try {
            $this->interviewResult = app(AiService::class)
                ->simulateInterview(
                    auth()->user(),
                    trim($this->interviewJobTitle),
                    trim($this->interviewJobDescription)
                );

            if (empty($this->interviewResult)) {
                $this->errorMessage = 'A IA não retornou perguntas. Tente novamente.';
            }
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Erro ao chamar a IA: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function replyEmail(): void
    {
        $this->errorMessage = '';
        $this->emailResult = [];

        if (trim($this->emailIncoming) === '') {
            $this->errorMessage = 'Cole o texto do e-mail recebido.';
            return;
        }

        $this->loading = true;

        try {
            $this->emailResult = app(AiService::class)
                ->replyEmail($this->emailIncoming, $this->emailIntention);
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Erro ao chamar a IA: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function careerPlan(): void
    {
        $this->errorMessage = '';
        $this->careerResult = [];
        $this->loading = true;

        try {
            $this->careerResult = app(AiService::class)
                ->careerPlan(auth()->user(), $this->careerGoal ?: null);
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Erro ao chamar a IA: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Aplica a bio otimizada do LinkedIn direto no perfil do usuário.
     * Uso: usuário fica satisfeito com o resultado e clica "Salvar no meu perfil".
     */
    public function saveLinkedInToProfile(): void
    {
        $u = auth()->user();
        $profile = $u?->candidateProfile;

        if (! $profile || empty($this->linkedinResult['about'])) {
            $this->errorMessage = 'Nada para salvar.';
            return;
        }

        if (! empty($this->linkedinResult['headline'])) {
            $u->headline = mb_substr($this->linkedinResult['headline'], 0, 220);
            $u->save();
        }
        $profile->bio = $this->linkedinResult['about'];
        $profile->save();

        session()->flash('success', 'Perfil atualizado com sucesso!');
    }

    public function render()
    {
        $jobsForSelect = JobListing::query()
            ->where('status', 'open')
            ->latest('published_at')
            ->limit(50)
            ->get(['id', 'title']);

        return view('livewire.ai.assistant', [
            'jobsForSelect' => $jobsForSelect,
        ]);
    }
}
