<?php

declare(strict_types=1);

namespace App\Livewire\Jobs;

use App\Models\Application;
use App\Models\JobListing;
use App\Services\JobMatchingService;
use App\Services\PointsService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Vaga · SocialJobs')]
class Show extends Component
{
    public ?JobListing $job = null;

    public function mount(?JobListing $job = null): void
    {
        $this->job = $job?->loadMissing(['companyProfile.user', 'skills']);
    }

    /**
     * Candidata o usuário logado à vaga (idempotente via unique key).
     */
    public function apply(): void
    {
        if (! auth()->check() || ! $this->job) {
            return;
        }

        if ((auth()->user()->type ?? '') !== 'candidate') {
            $this->addError('apply', 'Apenas candidatos podem se candidatar a vagas.');
            return;
        }

        $application = Application::query()->firstOrCreate([
            'user_id'        => auth()->id(),
            'job_listing_id' => $this->job->id,
        ], ['status' => 'received']);

        // Só concede XP se a candidatura foi criada agora (não repete)
        if ($application->wasRecentlyCreated) {
            app(PointsService::class)->award(
                auth()->user(),
                'application.sent',
                $application,
                'application.sent:' . $application->id
            );
            session()->flash('status', 'Candidatura enviada!');
        } else {
            session()->flash('status', 'Você já se candidatou a esta vaga.');
        }
    }

    public function render()
    {
        $matchScore = null;

        if (auth()->check() && $this->job) {
            $matchScore = (int) round(
                app(JobMatchingService::class)->scoreFor(auth()->user(), $this->job)
            );
        }

        $alreadyApplied = false;
        if (auth()->check() && $this->job) {
            $alreadyApplied = Application::query()
                ->where('user_id', auth()->id())
                ->where('job_listing_id', $this->job->id)
                ->exists();
        }

        return view('livewire.jobs.show', [
            'matchScore'     => $matchScore,
            'alreadyApplied' => $alreadyApplied,
        ]);
    }
}
