<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillAssessmentQuestion extends Model
{
    protected $fillable = [
        'skill_assessment_id', 'statement', 'options',
        'correct_index', 'explanation', 'position',
    ];

    protected $casts = [
        'options'       => 'array',
        'correct_index' => 'integer',
        'position'      => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(SkillAssessment::class, 'skill_assessment_id');
    }
}
