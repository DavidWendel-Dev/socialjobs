<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Experience extends Model
{
    protected $fillable = [
        'candidate_profile_id', 'company_name', 'role',
        'start_date', 'end_date', 'description', 'current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'current'    => 'boolean',
    ];

    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }
}
