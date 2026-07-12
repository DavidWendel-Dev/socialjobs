<?php

declare(strict_types=1);

namespace App\Livewire\Jobs;

use App\Models\JobListing;
use App\Services\JobMatchingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Vagas · SocialJobs')]
class Browse extends Component
{
    use WithPagination;

    // Filtros persistidos na URL (permite bookmark / compartilhar link)
    #[Url] public string $seniority = '';      // junior|mid|senior|lead
    #[Url] public string $modality = '';       // remote|hybrid|onsite
    #[Url] public string $contract_type = '';  // clt|pj|freelance|internship
    #[Url] public string $location = '';
    #[Url] public string $q = '';

    /**
     * Rótulos amigáveis em pt-BR para cada valor do banco.
     * Usado tanto no filtro quanto no card.
     */
    public array $seniorityLabels = [
        'junior' => 'Júnior',
        'mid'    => 'Pleno',
        'senior' => 'Sênior',
        'lead'   => 'Lead',
    ];

    public array $modalityLabels = [
        'remote' => 'Remoto',
        'hybrid' => 'Híbrido',
        'onsite' => 'Presencial',
    ];

    public array $contractLabels = [
        'clt'        => 'CLT',
        'pj'         => 'PJ',
        'freelance'  => 'Freelance',
        'internship' => 'Estágio',
    ];

    public function updating($property): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['seniority', 'modality', 'contract_type', 'location', 'q']);
    }

    public function render()
    {
        $query = JobListing::query()
            ->with('companyProfile')
            ->where('status', 'open');

        if ($this->seniority) {
            $query->where('seniority', $this->seniority);
        }
        if ($this->modality) {
            $query->where('modality', $this->modality);
        }
        if ($this->contract_type) {
            $query->where('contract_type', $this->contract_type);
        }
        if ($this->location) {
            $query->where('location', 'like', '%' . $this->location . '%');
        }
        if ($this->q) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->q . '%')
                  ->orWhere('description', 'like', '%' . $this->q . '%');
            });
        }

        $jobs = $query
            ->latest('published_at')
            ->latest('id')
            ->paginate(12);

        $matcher = auth()->check() ? app(JobMatchingService::class) : null;

        return view('livewire.jobs.browse', [
            'jobs'    => $jobs,
            'matcher' => $matcher,
        ]);
    }
}
