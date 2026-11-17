<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'attempt_question_id',
        'attempt_question_option_id',
        'essay_answer',
        'is_correct',
        'points_earned',
        'grader_notes',
        'graded_by',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'points_earned' => 'decimal:2',
            'graded_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function attemptQuestion(): BelongsTo
    {
        return $this->belongsTo(AttemptQuestion::class, 'attempt_question_id');
    }

    public function attemptQuestionOption(): BelongsTo
    {
        return $this->belongsTo(AttemptQuestionOption::class, 'attempt_question_option_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function isMultipleChoice(): bool
    {
        return $this->attempt_question_option_id !== null;
    }

    public function isEssay(): bool
    {
        return $this->attempt_question_option_id === null;
    }
}
