<div>
    @include('livewire.profile.partials.curriculum-body', [
        'user'              => $user,
        'about'             => $about,
        'stats'             => $stats,
        'skills'            => $skills,
        'experiences'       => $experiences,
        'educations'        => $educations,
        'portfolio'         => $portfolio,
        'courses_completed' => $courses_completed,
        'interviews'        => $interviews,
        'skill_badges'      => $skill_badges,
        'featured_posts'    => $featured_posts,
    ])
</div>
