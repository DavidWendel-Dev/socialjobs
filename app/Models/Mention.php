<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mention extends Model
{
    protected $fillable = [
        'mentioner_id',
        'mentioned_id',
        'mentionable_type',
        'mentionable_id',
    ];

    public function mentioner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioner_id');
    }

    public function mentioned(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_id');
    }

    public function mentionable(): MorphTo
    {
        return $this->morphTo();
    }
}
