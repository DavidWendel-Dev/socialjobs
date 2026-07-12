<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    protected $fillable = [
        'author_id', 'title', 'slug', 'summary', 'description',
        'thumbnail_path', 'category', 'level', 'total_minutes',
        'xp_reward', 'status', 'published_at',
        'owner_type', 'company_profile_id', 'visibility',
        'access_token', 'job_listing_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $course) {
            if ($course->owner_type === 'company') {
                if (empty($course->slug) && ! empty($course->title)) {
                    $course->slug = Str::slug($course->title) . '-' . uniqid();
                }
                if (empty($course->access_token)) {
                    $course->access_token = Str::random(48);
                }
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function companyProfile(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CompanyProfile::class);
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\JobListing::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('position');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /* -------------------- Scopes -------------------- */
    public function scopePlatform($q)
    {
        return $q->where('owner_type', 'platform');
    }

    public function scopeCompanyOwned($q)
    {
        return $q->where('owner_type', 'company');
    }

    public function scopePublic($q)
    {
        return $q->where('visibility', 'public');
    }

    /* -------------------- Helpers -------------------- */
    public function isCompanyOwned(): bool
    {
        return $this->owner_type === 'company';
    }

    public function inviteUrl(): string
    {
        return $this->access_token
            ? url('/enroll/' . $this->access_token)
            : url('/courses/' . $this->slug);
    }
}
