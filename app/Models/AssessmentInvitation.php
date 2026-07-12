<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Convite de empresa para candidato realizar um Skill Assessment.
 * Ver migration create_assessment_invitations_table para o fluxo completo.
 */
class AssessmentInvitation extends Model
{
    protected $fillable = [
        'skill_assessment_id', 'company_profile_id', 'job_application_id',
        'candidate_user_id', 'candidate_email', 'token', 'status',
        'expires_at', 'opened_at', 'completed_at', 'attempt_id',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'opened_at'    => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $inv) {
            if (empty($inv->token)) {
                $inv->token = Str::random(48);
            }
            if (empty($inv->status)) {
                $inv->status = 'pending';
            }
        });
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(SkillAssessment::class, 'skill_assessment_id');
    }

    public function companyProfile(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_user_id');
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(SkillAssessmentAttempt::class, 'attempt_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function invitationUrl(): string
    {
        return route('assessments.take-invite', ['token' => $this->token]);
    }
}
