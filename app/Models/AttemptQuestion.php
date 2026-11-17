<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttemptQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'question_snapshot',
        'options_snapshot',
        'order',
        'points_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'points_snapshot' => 'integer',
            'options_snapshot' => 'json',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function attemptOptions(): HasMany
    {
        return $this->hasMany(AttemptQuestionOption::class)->orderBy('order');
    }

    public function answer(): HasMany
    {
        return $this->hasMany(Answer::class, 'attempt_question_id');
    }
}
