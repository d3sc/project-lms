<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function show(Exam $exam): View|RedirectResponse
    {
        $user = auth()->user();
        abort_unless($exam->isPublished(), 404, 'Ujian tidak tersedia.');
        $active = ExamAttempt::where('exam_id', $exam->id)->where('user_id', $user->id)->where('status', 'in_progress')->first();
        if ($active) {
            if ($active->hasExpired()) {
                app(ExamAttemptService::class)->submit($active, true);
            } else {
                return to_route('student.exams.attempt', $active);
            }
        }
        abort_if(ExamAttempt::where('exam_id', $exam->id)->where('user_id', $user->id)->count() >= $exam->max_attempts, 403, 'Batas percobaan ujian ini sudah tercapai.');
        $exam->loadCount('questions');

        return view('student.exams.show', compact('exam'));
    }

    public function start(Exam $exam, ExamAttemptService $service): RedirectResponse
    {
        abort_unless($exam->isOpen(), 404, 'Ujian tidak dapat dimulai.');

        return to_route('student.exams.attempt', $service->start(auth()->user(), $exam));
    }

    public function attempt(ExamAttempt $attempt): View|RedirectResponse
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        abort_unless($attempt->isInProgress(), 403);
        if ($attempt->hasExpired()) {
            app(ExamAttemptService::class)->submit($attempt, true);

            return to_route('student.exams.results', $attempt);
        }
        $attempt->load(['exam', 'attemptQuestions' => fn ($q) => $q->with('attemptOptions')->orderBy('order'), 'answers']);

        return view('student.exams.attempt', compact('attempt'));
    }

    public function saveAnswer(Request $request, ExamAttempt $attempt, ExamAttemptService $service): JsonResponse
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        abort_unless($attempt->isInProgress(), 403);
        if ($attempt->hasExpired()) {
            $service->submit($attempt, true);

            return response()->json(['expired' => true], 410);
        }
        $data = $request->validate([
            'attempt_question_id' => ['required', 'integer', 'exists:attempt_questions,id'],
            'option_id' => ['nullable', 'integer', 'exists:attempt_question_options,id'],
            'essay_answer' => ['nullable', 'string'],
        ]);
        $answer = $service->saveAnswer($attempt, auth()->user(), $data['attempt_question_id'], $data['option_id'] ?? null, $data['essay_answer'] ?? null);

        return response()->json(['saved' => true, 'answer_id' => $answer->id, 'server_time' => now()->toIso8601String(), 'remaining_seconds' => $attempt->remainingSeconds()]);
    }

    public function submit(ExamAttempt $attempt, ExamAttemptService $service): JsonResponse
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        abort_unless($attempt->isInProgress(), 403);
        $submitted = $service->submit($attempt);

        return response()->json(['submitted' => true, 'redirect' => route('student.exams.results', $submitted)]);
    }

    public function results(ExamAttempt $attempt): View
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        abort_unless(in_array($attempt->status, ['submitted', 'grading', 'graded'], true), 403);
        $attempt->load(['exam', 'attemptQuestions.attemptOptions', 'attemptQuestions.answer']);

        return view('student.exams.results', ['attempt' => $attempt, 'showResult' => $attempt->exam->show_result, 'showAnswers' => $attempt->exam->show_correct_answers]);
    }
}
