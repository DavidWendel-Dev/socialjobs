<?php

declare(strict_types=1);

namespace App\Livewire\Company\Assessments;

use App\Models\SkillAssessment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Meus testes · SocialJobs')]
class Manage extends Component
{
    public function delete(int $id): void
    {
        $cp = auth()->user()?->companyProfile;
        if (! $cp) {
            return;
        }

        $affected = SkillAssessment::query()
            ->where('id', $id)
            ->where('owner_type', 'company')
            ->where('company_profile_id', $cp->id)
            ->delete();

        if ($affected > 0) {
            session()->flash('status', 'Teste removido.');
        } else {
            session()->flash('error', 'Teste não encontrado ou sem permissão.');
        }
    }

    public function render(): View
    {
        $cp = auth()->user()?->companyProfile;

        $assessments = $cp
            ? SkillAssessment::query()
                ->companyOwned()
                ->where('company_profile_id', $cp->id)
                ->withCount(['invitations', 'attempts'])
                ->latest()
                ->get()
            : collect();

        return view('livewire.company.assessments.manage', [
            'assessments' => $assessments,
        ]);
    }
}
