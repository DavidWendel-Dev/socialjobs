<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Um "Skill Assessment" — teste de proficiência que qualquer usuário
 * pode fazer para comprovar conhecimento em uma habilidade específica.
 * O melhor score aparece no perfil e no Currículo Digital com selo verificado.
 *
 * A partir de 07/2026, empresas também podem criar seus próprios testes
 * (owner_type='company') — usados para triagem via convite.
 */
class SkillAssessment extends Model
{
    protected $fillable = [
        'title', 'slug', 'category', 'short_description', 'description',
        'difficulty', 'icon', 'color',
        'duration_minutes', 'passing_score', 'xp_reward', 'is_active',
        'owner_type', 'company_profile_id', 'visibility',
        'job_listing_id', 'created_by_user_id',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'duration_minutes' => 'integer',
        'passing_score'    => 'integer',
        'xp_reward'        => 'integer',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(SkillAssessmentQuestion::class)->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(SkillAssessmentAttempt::class);
    }

    public function companyProfile(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class);
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(AssessmentInvitation::class);
    }

    public function isCompanyOwned(): bool
    {
        return $this->owner_type === 'company';
    }

    /**
     * Escopo: apenas testes do catálogo da plataforma.
     */
    public function scopePlatform($q)
    {
        return $q->where('owner_type', 'platform');
    }

    /**
     * Escopo: apenas testes criados por empresas.
     */
    public function scopeCompanyOwned($q)
    {
        return $q->where('owner_type', 'company');
    }

    /**
     * Melhor score do usuário neste teste (null se nunca fez).
     */
    public function bestScoreFor(User $user): ?int
    {
        $best = $this->attempts()
            ->where('user_id', $user->id)
            ->max('score');

        return $best !== null ? (int) $best : null;
    }

    /**
     * Rótulo de dificuldade em PT-BR.
     */
    public function difficultyLabel(): string
    {
        return match ($this->difficulty) {
            'basic'        => 'Básico',
            'advanced'     => 'Avançado',
            default        => 'Intermediário',
        };
    }
}
