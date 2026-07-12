<?php

namespace App\Models;

use App\Models\Concerns\HasReactions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class JobListing extends Model
{
    use HasReactions;
    use Searchable;

    protected $fillable = [
        'company_profile_id', 'title', 'slug', 'description',
        'seniority', 'modality', 'contract_type', 'location',
        'salary_min', 'salary_max', 'status', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'salary_min'   => 'decimal:2',
        'salary_max'   => 'decimal:2',
    ];

    public function companyProfile(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_listing_skill');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Dados indexados no Scout (busca full-text).
     *
     * @return array<string,mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => strip_tags((string) $this->description),
            'location'    => $this->location,
            'modality'    => $this->modality,
            'seniority'   => $this->seniority,
            'status'      => $this->status,
        ];
    }
}
