<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'quiz_id', 'score', 'passed', 'answers', 'created_at'];

    protected $casts = [
        'passed'     => 'boolean',
        // 'encrypted' porque respostas podem revelar padrões de raciocínio do usuário
        'answers'    => 'encrypted:array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
