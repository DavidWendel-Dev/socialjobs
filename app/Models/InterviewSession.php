<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewSession extends Model
{
    protected $fillable = [
        'user_id', 'role_title', 'seniority', 'job_listing_id',
        'mode', 'status', 'started_at', 'finished_at',
        'overall_score', 'scores', 'feedback',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
        // scores JSON criptografado — pode conter avaliação sensível
        'scores'      => 'encrypted:array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function turns(): HasMany
    {
        return $this->hasMany(InterviewTurn::class, 'session_id')->orderBy('position');
    }
}
