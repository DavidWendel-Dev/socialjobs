<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyReview extends Model
{
    protected $fillable = [
        'company_profile_id', 'user_id', 'job_application_id',
        'rating_overall', 'rating_process', 'rating_communication', 'rating_culture',
        'title', 'pros', 'cons', 'would_recommend', 'is_anonymous', 'is_published',
        'company_response', 'company_responded_at',
    ];

    protected $casts = [
        'would_recommend'      => 'boolean',
        'is_anonymous'         => 'boolean',
        'is_published'         => 'boolean',
        'company_responded_at' => 'datetime',
    ];

    public function companyProfile(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'job_application_id');
    }
}
