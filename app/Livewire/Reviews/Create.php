<?php

declare(strict_types=1);

namespace App\Livewire\Reviews;

use App\Models\Application;
use App\Models\CompanyProfile;
use App\Models\CompanyReview;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Avaliar empresa · SocialJobs')]
class Create extends Component
{
    public CompanyProfile $company;

    public int $ratingOverall = 5;
    public int $ratingProcess = 5;
    public int $ratingCommunication = 5;
    public int $ratingCulture = 5;

    public string $title = '';
    public string $pros = '';
    public string $cons = '';
    public bool $wouldRecommend = true;
    public bool $isAnonymous = false;

    public function mount(CompanyProfile $company): void
    {
        abort_unless(auth()->check() && (auth()->user()->type ?? '') === 'candidate', 403);
        $this->company = $company;

        $hasApp = Application::whereHas('jobListing', fn ($q) => $q->where('company_profile_id', $company->id))
            ->where('user_id', auth()->id())
            ->whereIn('status', ['interview', 'offer', 'hired', 'rejected'])
            ->exists();

        abort_unless($hasApp, 403, 'Você só pode avaliar empresas com as quais teve processo seletivo.');

        $existing = CompanyReview::where('user_id', auth()->id())
            ->where('company_profile_id', $company->id)
            ->first();

        if ($existing) {
            session()->flash('status', 'Você já avaliou esta empresa.');
            $this->redirect(url('/c/' . $company->slug));
        }
    }

    public function setRating(string $field, int $value): void
    {
        $value = max(1, min(5, $value));
        if (in_array($field, ['ratingOverall', 'ratingProcess', 'ratingCommunication', 'ratingCulture'], true)) {
            $this->{$field} = $value;
        }
    }

    public function save(): void
    {
        $this->validate([
            'ratingOverall'       => 'required|integer|min:1|max:5',
            'ratingProcess'       => 'required|integer|min:1|max:5',
            'ratingCommunication' => 'required|integer|min:1|max:5',
            'ratingCulture'       => 'required|integer|min:1|max:5',
            'title'               => 'required|string|max:191',
            'pros'                => 'required|string|max:2000',
            'cons'                => 'required|string|max:2000',
        ]);

        CompanyReview::create([
            'company_profile_id'   => $this->company->id,
            'user_id'              => auth()->id(),
            'rating_overall'       => $this->ratingOverall,
            'rating_process'       => $this->ratingProcess,
            'rating_communication' => $this->ratingCommunication,
            'rating_culture'       => $this->ratingCulture,
            'title'                => $this->title,
            'pros'                 => $this->pros,
            'cons'                 => $this->cons,
            'would_recommend'      => $this->wouldRecommend,
            'is_anonymous'         => $this->isAnonymous,
        ]);

        session()->flash('status', 'Avaliação enviada! Obrigado por contribuir.');
        $this->redirect(url('/c/' . $this->company->slug));
    }

    public function render()
    {
        return view('livewire.reviews.create');
    }
}
