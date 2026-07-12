<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Support\Media;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'type',
        'avatar_path',
        'cover_path',
        'headline',
        'location',
        'is_verified',
        'open_to_work',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'password'                   => 'hashed',
            'is_verified'                => 'boolean',
            'open_to_work'               => 'boolean',
            // Criptografa segredos do 2FA em repouso
            'two_factor_secret'          => 'encrypted',
            'two_factor_recovery_codes'  => 'encrypted',
            'two_factor_confirmed_at'    => 'datetime',
        ];
    }

    /* ============================================================
     |  Boot hooks
     |============================================================ */

    /**
     * Garante que todo usuário tenha um `username` único e amigável para URL,
     * mesmo quando o Breeze/registro padrão não pede esse campo.
     */
    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (empty($user->username)) {
                $user->username = self::generateUniqueUsername($user->name ?? $user->email ?? 'user');
            }
        });

        static::saving(function (self $user): void {
            // Recupera perfis órfãos: se por algum motivo o username ficou vazio,
            // preenche antes de persistir.
            if (empty($user->username)) {
                $user->username = self::generateUniqueUsername($user->name ?? $user->email ?? 'user');
            }
        });

        // Ao criar um usuário, garante um perfil correspondente (candidato ou empresa).
        // Isso evita 404 na página de perfil logo após o cadastro pelo Breeze.
        static::created(function (self $user): void {
            $type = $user->type ?? 'candidate';

            if ($type === 'company' && ! $user->companyProfile) {
                CompanyProfile::create([
                    'user_id'    => $user->id,
                    'legal_name' => $user->name ?? 'Empresa',
                    'slug'       => self::generateUniqueCompanySlug($user->name ?? 'empresa', $user->id),
                ]);
            } elseif ($type !== 'admin' && ! $user->candidateProfile) {
                CandidateProfile::create([
                    'user_id' => $user->id,
                ]);
            }
        });
    }

    /**
     * Gera um slug único para company_profiles, evitando colisões.
     */
    protected static function generateUniqueCompanySlug(string $seed, int $userId): string
    {
        $base      = Str::slug($seed) ?: 'empresa';
        $candidate = $base;
        $suffix    = 1;

        while (CompanyProfile::query()->where('slug', $candidate)->exists()) {
            $candidate = $base . '-' . $suffix++;
            if ($suffix > 200) {
                $candidate = $base . '-' . $userId;
                break;
            }
        }

        return $candidate;
    }

    /**
     * Gera um slug de username único a partir do nome, garantindo que
     * não colida com registros existentes.
     */
    public static function generateUniqueUsername(string $seed): string
    {
        // Remove tudo antes do @ se for email
        if (str_contains($seed, '@')) {
            $seed = strstr($seed, '@', true) ?: $seed;
        }

        $base = Str::slug($seed) ?: 'user';
        $base = Str::limit($base, 40, '');

        $candidate = $base;
        $suffix    = 1;

        while (self::query()->where('username', $candidate)->exists()) {
            $candidate = $base . '-' . $suffix++;
            if ($suffix > 9999) {
                // Fallback extremo — garante unicidade
                $candidate = $base . '-' . Str::lower(Str::random(6));
                break;
            }
        }

        return $candidate;
    }

    /**
     * Usa `username` como chave nas URLs (route model binding).
     * Se o valor buscado for numérico, cai no id (compatibilidade).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: 'username';

        return $this
            ->where($field, $value)
            ->orWhere('id', is_numeric($value) ? (int) $value : 0)
            ->first();
    }

    /* ============================================================
     |  Relacionamentos
     |============================================================ */

    public function candidateProfile(): HasOne
    {
        return $this->hasOne(CandidateProfile::class);
    }

    public function companyProfile(): HasOne
    {
        return $this->hasOne(CompanyProfile::class);
    }

    // ==================== BLOQUEIOS ====================

    /** Bloqueios que EU fiz (blocker_id = eu). */
    public function blocksMade(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocker_id');
    }

    /** Bloqueios que EU sofri (blocked_id = eu). */
    public function blocksReceived(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocked_id');
    }

    /** Usuários que EU bloqueei. */
    public function blockedUsers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'blocker_id', 'blocked_id');
    }

    /** Retorna true se eu bloqueei $other. */
    public function hasBlocked(User|int|null $other): bool
    {
        if (! $other) return false;
        $id = $other instanceof User ? $other->id : (int) $other;
        return UserBlock::where('blocker_id', $this->id)->where('blocked_id', $id)->exists();
    }

    /** Retorna true se $other me bloqueou. */
    public function isBlockedBy(User|int|null $other): bool
    {
        if (! $other) return false;
        $id = $other instanceof User ? $other->id : (int) $other;
        return UserBlock::where('blocker_id', $id)->where('blocked_id', $this->id)->exists();
    }

    // ==================== SILENCIAR ====================

    /** Retorna true se eu silenciei $other. */
    public function hasMuted(User|int|null $other): bool
    {
        if (! $other) return false;
        $id = $other instanceof User ? $other->id : (int) $other;
        return UserMute::where('muter_id', $this->id)->where('muted_id', $id)->exists();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Usuários que este usuário segue.
     */
    public function follows(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'follower_id',
            'followed_id'
        )->withPivot('created_at');
    }

    /**
     * Seguidores deste usuário.
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'followed_id',
            'follower_id'
        )->withPivot('created_at');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(UserStat::class);
    }

    public function pointEvents(): HasMany
    {
        return $this->hasMany(PointEvent::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'badge_user')->withPivot('earned_at');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function savedJobs(): BelongsToMany
    {
        return $this->belongsToMany(JobListing::class, 'saved_jobs')->withPivot('created_at');
    }

    /* ============================================================
     |  Scopes
     |============================================================ */

    public function scopeCandidates(Builder $q): Builder
    {
        return $q->where('type', 'candidate');
    }

    public function scopeCompanies(Builder $q): Builder
    {
        return $q->where('type', 'company');
    }

    /* ============================================================
     |  Accessors
     |============================================================ */

    /**
     * URL pública do avatar. Se o usuário não fez upload, retorna null
     * (o componente <x-avatar/> mostra as iniciais coloridas).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar_path)) {
            return null;
        }

        return Media::url($this->avatar_path);
    }

    /**
     * Nome público exibido em posts, comentários, chat, etc.
     * - Empresa: nome fantasia (trade_name) → razão social (legal_name) → name do user
     * - Candidato: nome do usuário
     */
    public function getDisplayNameAttribute(): string
    {
        if (($this->type ?? 'candidate') === 'company') {
            $company = $this->relationLoaded('companyProfile') ? $this->companyProfile : $this->companyProfile()->first();
            if ($company) {
                $trade = trim((string) ($company->trade_name ?? ''));
                if ($trade !== '') {
                    return $trade;
                }
                $legal = trim((string) ($company->legal_name ?? ''));
                if ($legal !== '') {
                    return $legal;
                }
            }
        }
        return (string) ($this->name ?? 'Usuário');
    }

    /**
     * URL pública da capa.
     */
    public function getCoverUrlAttribute(): ?string
    {
        if (empty($this->cover_path)) {
            return null;
        }

        return Media::url($this->cover_path);
    }
}
