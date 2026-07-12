<?php

namespace App\Models;

use App\Models\Concerns\HasReactions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasReactions;

    protected $fillable = ['user_id', 'body', 'type', 'is_featured', 'visibility', 'link_preview'];

    protected $casts = [
        'link_preview' => 'array',
    ];

    /**
     * Aplica automaticamente o filtro que esconde posts de usuários bloqueados
     * (nos dois sentidos: quem eu bloqueei + quem me bloqueou).
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\HideBlockedPostsScope());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('order')->orderBy('id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
