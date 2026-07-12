<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillAssessmentAttempt extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'user_id', 'skill_assessment_id', 'score', 'passed',
        'answers', 'duration_seconds', 'started_at', 'finished_at',
        'tab_leaves', 'copy_attempts', 'devtools_opens', 'integrity_status',
    ];

    protected $casts = [
        'passed'           => 'boolean',
        'answers'          => 'array',
        'score'            => 'integer',
        'duration_seconds' => 'integer',
        'tab_leaves'       => 'integer',
        'copy_attempts'    => 'integer',
        'devtools_opens'   => 'integer',
        'started_at'       => 'datetime',
        'finished_at'      => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(SkillAssessment::class, 'skill_assessment_id');
    }
}
