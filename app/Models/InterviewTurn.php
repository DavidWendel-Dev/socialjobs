<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewTurn extends Model
{
    public $timestamps = false;

    protected $fillable = ['session_id', 'role', 'body', 'audio_path', 'position', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(InterviewSession::class, 'session_id');
    }
}
