<?php

declare(strict_types=1);

namespace App\Livewire\Jobs;

use App\Models\JobListing;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Publicar vaga — wizard de 3 passos.
 *
 * Passo 1: Informações básicas (título, senioridade, modalidade, contrato, localização)
 * Passo 2: Descrição da vaga (com melhorar via IA)
 * Passo 3: Faixa salarial (opcional) + publicar
 *
 * Todos os campos abaixo correspondem 1:1 com o schema real de `job_listings`:
 *  - title, slug, description
 *  - seniority: junior|mid|senior|lead
 *  - modality:  remote|hybrid|onsite
 *  - contract_type: clt|pj|freelance|internship
 *  - location (nullable), salary_min, salary_max (decimals)
 *  - status = 'open' ao publicar; published_at = now()
 */
#[Layout('layouts.app')]
#[Title('Publicar vaga · SocialJobs')]
class Create extends Component
{
    public int $step = 1;

    #[Validate('required|string|min:3|max:180')]
    public string $title = '';

    #[Validate('required|in:junior,mid,senior,lead')]
    public string $seniority = 'mid';

    #[Validate('required|in:remote,hybrid,onsite')]
    public string $modality = 'remote';

    #[Validate('required|in:clt,pj,freelance,internship')]
    public string $contractType = 'clt';

    #[Validate('nullable|string|max:180')]
    public string $location = '';

    #[Validate('required|string|min:20|max:20000')]
    public string $description = '';

    #[Validate('nullable|numeric|min:0|max:9999999')]
    public ?float $salaryMin = null;

    #[Validate('nullable|numeric|min:0|max:9999999')]
    public ?float $salaryMax = null;

    public string $flashError = '';

    /* ============================================================
     |  Navegação do wizard
     * ============================================================ */
    public function next(): void
    {
        $this->flashError = '';

        // Valida os campos do passo atual antes de avançar
        if ($this->step === 1) {
            $this->validateOnly('title');
            $this->validateOnly('seniority');
            $this->validateOnly('modality');
            $this->validateOnly('contractType');
            $this->validateOnly('location');
        } elseif ($this->step === 2) {
            $this->validateOnly('description');
        }

        $this->step = min($this->step + 1, 3);
    }

    public function back(): void
    {
        $this->step = max($this->step - 1, 1);
    }

    /* ============================================================
     |  IA — melhorar descrição
     * ============================================================ */
    public function improveWithAi(): void
    {
        if (trim($this->description) === '') {
            $this->addError('description', 'Escreva algo primeiro para a IA melhorar.');
            return;
        }

        try {
            if (class_exists(\App\Services\AiService::class)) {
                $svc = app(\App\Services\AiService::class);
                if (method_exists($svc, 'improveJobDescription')) {
                    $improved = (string) $svc->improveJobDescription($this->description);
                    if ($improved !== '') {
                        $this->description = $improved;
                        session()->flash('status', 'Descrição melhorada com IA.');
                        return;
                    }
                }
            }
            session()->flash('status', 'IA indisponível no momento.');
        } catch (\Throwable $e) {
            session()->flash('status', 'Não foi possível melhorar com IA agora.');
        }
    }

    /* ============================================================
     |  Publicar
     * ============================================================ */
    public function publish(): void
    {
        $this->validate();

        // Empresa
        $cp = auth()->user()?->companyProfile;
        if (! $cp) {
            $this->flashError = 'Apenas empresas podem publicar vagas.';
            return;
        }

        // Salary_max deve ser >= salary_min quando ambos preenchidos
        if ($this->salaryMin !== null && $this->salaryMax !== null && $this->salaryMax < $this->salaryMin) {
            $this->addError('salaryMax', 'O salário máximo deve ser maior ou igual ao mínimo.');
            $this->step = 3;
            return;
        }

        try {
            $slug = Str::slug($this->title) ?: 'vaga';
            $slug = $slug . '-' . substr(uniqid(), -6);

            $job = JobListing::query()->create([
                'company_profile_id' => $cp->id,
                'title'              => $this->title,
                'slug'               => $slug,
                'description'        => $this->description,
                'seniority'          => $this->seniority,
                'modality'           => $this->modality,
                'contract_type'      => $this->contractType,
                'location'           => $this->location !== '' ? $this->location : null,
                'salary_min'         => $this->salaryMin,
                'salary_max'         => $this->salaryMax,
                'status'             => 'open',
                'published_at'       => now(),
            ]);

            session()->flash('status', 'Vaga publicada com sucesso!');
            $this->redirectRoute('jobs.show', ['job' => $job->slug], navigate: true);
        } catch (\Throwable $e) {
            report($e);
            $this->flashError = 'Não foi possível publicar a vaga: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.jobs.create');
    }
}
