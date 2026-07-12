<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStat extends Model
{
    // A tabela usa user_id como PK (não incremental)
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = [
        'user_id', 'total_xp', 'level',
        'login_streak', 'last_login_at', 'updated_at',
    ];

    protected $casts = [
        'last_login_at' => 'date',
        'updated_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
