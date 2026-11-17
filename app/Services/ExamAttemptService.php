<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\AttemptQuestion;
use App\Models\AttemptQuestionOption;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExamAttemptService
{
    /**
     * Start a new attempt, or return the existing in-progress attempt
     * (one active attempt per user per exam).
     */
    public function start(User $user, Exam $exam): ExamAttempt
    {
        if (! $exam->isOpen()) {
            throw new RuntimeException('Ujian belum dibuka atau sudah ditutup.');
        }

        return DB::transaction(function () use ($user, $exam) {
            // Lock the exam row to avoid concurrent attempt creation races.
            $locked = Exam::whereKey($exam->id)->lockForUpdate()->firstOrFail();

            $active = ExamAttempt::where('exam_id', $locked->id)
                ->where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->first();

            if ($active) {
                if ($active->hasExpired()) {
                    $this->submit($active, true);

                    return $this->createAttempt($user, $locked);
                }

                return $active;
            }

            $used = ExamAttempt::where('exam_id', $locked->id)
                ->where('user_id', $user->id)
                ->count();

            if ($used >= $locked->max_attempts) {
                throw new RuntimeException('Batas percobaan ujian ini sudah tercapai.');
            }

            return $this->createAttempt($user, $locked);
        });
    }

    protected function createAttempt(User $user, Exam $exam): ExamAttempt
    {
        $attemptNumber = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->max('attempt_number') + 1;

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
            'attempt_number' => $attemptNumber,
            'status' => 'in_progress',
            'started_at' => now(),
            'expires_at' => $exam->duration_minutes ? now()->addMinutes($exam->duration_minutes) : null,
            'max_score' => $exam->totalPoints(),
        ]);

        $this->snapshotQuestions($attempt, $exam);

        return $attempt->fresh();
    }

    /**
     * Freeze question order and option order for this attempt so that
     * a browser refresh (or a new tab) always sees the same sequence.
     */
    protected function snapshotQuestions(ExamAttempt $attempt, Exam $exam): void
    {
        $questions = $exam->questions()->with('options')->get();

        if ($exam->randomize_questions) {
            $questions = $questions->shuffle();
        }

        foreach ($questions->values() as $index => $question) {
            $attemptQuestion = AttemptQuestion::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'question_snapshot' => $question->content,
                'order' => $index + 1,
                'points_snapshot' => $question->points,
            ]);

            $options = $question->options;

            if ($exam->randomize_options) {
                $options = $options->shuffle();
            }

            foreach ($options->values() as $optIndex => $option) {
                AttemptQuestionOption::create([
                    'attempt_question_id' => $attemptQuestion->id,
                    'option_id' => $option->id,
                    'content_snapshot' => $option->content,
                    'is_correct_snapshot' => $option->is_correct,
                    'order' => $optIndex + 1,
                ]);
            }
        }
    }

    /**
     * Save (or replace) an answer. Only the server decides ownership,
     * attempt state and expiry — nothing is trusted from the client.
     */
    public function saveAnswer(
        ExamAttempt $attempt,
        User $user,
        int $attemptQuestionId,
        ?int $attemptOptionId,
        ?string $essayAnswer
    ): Answer {
        if ($attempt->user_id !== $user->id) {
            throw new RuntimeException('Unauthorized.');
        }

        if (! $attempt->isInProgress()) {
            throw new RuntimeException('Ujian sudah dikumpulkan.');
        }

        if ($attempt->hasExpired()) {
            $this->submit($attempt, true);
            throw new RuntimeException('Waktu ujian sudah habis.');
        }

        $attemptQuestion = $attempt->attemptQuestions()
            ->whereKey($attemptQuestionId)
            ->firstOrFail();

        $data = [
            'attempt_id' => $attempt->id,
            'attempt_question_id' => $attemptQuestion->id,
        ];

        if ($attemptOptionId !== null) {
            // Verify the option belongs to this attempt question (IDOR guard).
            $option = AttemptQuestionOption::whereKey($attemptOptionId)
                ->where('attempt_question_id', $attemptQuestion->id)
                ->firstOrFail();

            $data['attempt_question_option_id'] = $option->id;
            $data['essay_answer'] = null;
        } else {
            $data['attempt_question_option_id'] = null;
            $data['essay_answer'] = $essayAnswer;
        }

        return Answer::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'attempt_question_id' => $attemptQuestion->id,
            ],
            $data
        );
    }

    /**
     * Submit and grade. Multiple-choice is graded automatically;
     * essays put the attempt into "grading" until an admin scores them.
     */
    public function submit(ExamAttempt $attempt, bool $auto = false): ExamAttempt
    {
        if (! $attempt->isInProgress()) {
            return $attempt;
        }

        return DB::transaction(function () use ($attempt) {
            $attempt = ExamAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();

            if (! $attempt->isInProgress()) {
                return $attempt;
            }

            $autoGradeScore = 0.0;
            $needsManual = false;

            foreach ($attempt->attemptQuestions()->with('attemptOptions')->get() as $aq) {
                $answer = Answer::firstOrNew([
                    'attempt_id' => $attempt->id,
                    'attempt_question_id' => $aq->id,
                ]);

                $isEssay = $aq->attemptOptions->isEmpty();

                if ($isEssay) {
                    $needsManual = true;
                    $answer->is_correct = null;
                    $answer->points_earned = null;
                    $answer->save();

                    continue;
                }

                $selected = $answer->attempt_question_option_id;
                $correctOption = $aq->attemptOptions->firstWhere('is_correct_snapshot', true);

                if ($selected === null) {
                    $answer->is_correct = false;
                    $answer->points_earned = 0;
                } elseif ($correctOption && $selected === $correctOption->id) {
                    $answer->is_correct = true;
                    $answer->points_earned = $aq->points_snapshot;
                    $autoGradeScore += $aq->points_snapshot;
                } else {
                    $answer->is_correct = false;
                    $answer->points_earned = 0;
                }

                $answer->save();
            }

            $attempt->submitted_at = $attempt->submitted_at ?? now();
            $attempt->score = $autoGradeScore;

            if ($needsManual) {
                $attempt->status = 'grading';
            } else {
                $attempt->status = 'graded';
                $attempt->graded_at = now();
            }

            $attempt->save();

            return $attempt;
        });
    }

    /**
     * Auto-submit every expired in-progress attempt (used by the
     * scheduler and by the monitoring endpoint).
     */
    public function autoSubmitExpired(): int
    {
        $expired = ExamAttempt::where('status', 'in_progress')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expired as $attempt) {
            $this->submit($attempt, true);
        }

        return $expired->count();
    }
}
