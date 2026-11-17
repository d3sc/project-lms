<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'duration_minutes',
        'max_attempts',
        'randomize_questions',
        'randomize_options',
        'show_result',
        'show_correct_answers',
        'published_at',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'max_attempts' => 'integer',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'show_result' => 'boolean',
            'show_correct_answers' => 'boolean',
            'published_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    public function isOpen(): bool
    {
        if (! $this->isPublished()) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function totalPoints(): int
    {
        return (int) $this->questions()->sum('points');
    }

    /**
     * Label durasi untuk ditampilkan di UI.
     */
    public function durationLabel(): string
    {
        return $this->duration_minutes
            ? $this->duration_minutes.' menit'
            : 'Tanpa batas waktu';
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
