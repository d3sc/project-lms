<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamAttempt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'exam_id',
        'user_id',
        'attempt_number',
        'status',
        'started_at',
        'expires_at',
        'submitted_at',
        'graded_at',
        'score',
        'max_score',
        'graded_by',
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function attemptQuestions(): HasMany
    {
        return $this->hasMany(AttemptQuestion::class, 'attempt_id')->orderBy('order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'attempt_id');
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isGrading(): bool
    {
        return $this->status === 'grading';
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && now()->gt($this->expires_at);
    }

    /**
     * Sisa waktu dalam detik. Null berarti ujian tanpa batas waktu.
     */
    public function remainingSeconds(): ?int
    {
        if (! $this->started_at || ! $this->expires_at) {
            return null;
        }

        return max(0, (int) now()->diffInSeconds($this->expires_at, false));
    }

    public function isTimeLimited(): bool
    {
        return $this->expires_at !== null;
    }

    public function getIsSubmittedAttribute(): bool
    {
        return $this->status === 'submitted';
    }

    public function getMaxScoreAttribute($value)
    {
        return $value ?? 0;
    }

    public function percentageScore(): ?float
    {
        if ($this->max_score === 0 || $this->max_score === null) {
            return null;
        }

        return round(($this->score / $this->max_score) * 100, 2);
    }
}
