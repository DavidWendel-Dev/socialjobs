<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyProfile extends Model
{
    protected $fillable = [
        'user_id', 'cnpj', 'legal_name', 'trade_name', 'slug',
        'industry', 'size', 'website', 'phone', 'address',
        'logo_path', 'about', 'domain_verified',
    ];

    protected $casts = [
        'domain_verified' => 'boolean',
        'address'         => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(\App\Models\CompanyReview::class);
    }

    public function averageRating(): float
    {
        return (float) ($this->reviews()->where('is_published', true)->avg('rating_overall') ?? 0);
    }

    public function recommendationRate(): int
    {
        $total = $this->reviews()->where('is_published', true)->count();
        if ($total === 0) {
            return 0;
        }
        $yes = $this->reviews()->where('is_published', true)->where('would_recommend', true)->count();
        return (int) round(($yes / $total) * 100);
    }
}
