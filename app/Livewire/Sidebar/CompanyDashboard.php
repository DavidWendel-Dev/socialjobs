<?php
declare(strict_types=1);

namespace App\Livewire\Sidebar;

use App\Models\Application;
use App\Models\JobListing;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CompanyDashboard extends Component
{
    public function render(): View
    {
        $user = auth()->user();
        $cp   = $user?->companyProfile;

        if (! $cp) {
            return view('livewire.sidebar.company-dashboard', [
                'openJobsCount'        => 0,
                'totalJobs'            => 0,
                'newApplicationsCount' => 0,
                'inReviewCount'        => 0,
                'topJobs'              => collect(),
            ]);
        }

        $openJobsCount = JobListing::where('company_profile_id', $cp->id)
            ->where('status', 'open')
            ->count();

        $totalJobs = JobListing::where('company_profile_id', $cp->id)->count();

        $newApplicationsCount = Application::query()
            ->whereHas('jobListing', fn ($q) => $q->where('company_profile_id', $cp->id))
            ->where('status', 'received')
            ->count();

        $inReviewCount = Application::query()
            ->whereHas('jobListing', fn ($q) => $q->where('company_profile_id', $cp->id))
            ->whereIn('status', ['reviewing', 'interview'])
            ->count();

        // Top 3 vagas com mais candidaturas
        $topJobs = JobListing::query()
            ->where('company_profile_id', $cp->id)
            ->where('status', 'open')
            ->withCount('applications')
            ->orderByDesc('applications_count')
            ->limit(3)
            ->get();

        return view('livewire.sidebar.company-dashboard', compact(
            'openJobsCount', 'totalJobs', 'newApplicationsCount', 'inReviewCount', 'topJobs'
        ));
    }
}
