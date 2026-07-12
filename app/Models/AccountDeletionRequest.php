<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountDeletionRequest extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'reason', 'scheduled_for', 'cancelled_at', 'created_at'];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'cancelled_at'  => 'datetime',
        'created_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
