<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttemptQuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_question_id',
        'option_id',
        'content_snapshot',
        'is_correct_snapshot',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_correct_snapshot' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function attemptQuestion(): BelongsTo
    {
        return $this->belongsTo(AttemptQuestion::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'option_id');
    }
}
