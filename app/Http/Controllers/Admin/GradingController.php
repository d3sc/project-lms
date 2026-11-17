<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\ExamAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GradingController extends Controller
{
    public function index(): View
    {
        $attempts = ExamAttempt::with(['user', 'exam'])
            ->where('status', 'grading')
            ->orderBy('submitted_at')
            ->paginate(20);

        return view('admin.grading.index', compact('attempts'));
    }

    public function show(ExamAttempt $attempt): View
    {
        abort_unless($attempt->status === 'grading' || $attempt->status === 'graded', 404);

        $attempt->load([
            'user',
            'exam',
            'attemptQuestions.attemptOptions',
            'attemptQuestions.answer',
        ]);

        return view('admin.grading.show', compact('attempt'));
    }

    public function grade(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        abort_unless(in_array($attempt->status, ['grading', 'graded'], true), 404);

        $data = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.points' => ['required', 'numeric', 'min:0'],
            'answers.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $request, $attempt) {
            $total = 0.0;
            $complete = true;

            $essayQuestionIds = $attempt->attemptQuestions()
                ->withCount('attemptOptions')
                ->get()
                ->where('attempt_options_count', 0)
                ->pluck('id')
                ->all();

            foreach ($essayQuestionIds as $aqId) {
                $aq = $attempt->attemptQuestions()->whereKey($aqId)->firstOrFail();
                $input = $data['answers'][$aqId] ?? null;

                $answer = Answer::firstOrNew([
                    'attempt_id' => $attempt->id,
                    'attempt_question_id' => $aqId,
                ]);
                $answer->attempt_question_option_id = null;

                if ($input === null) {
                    $complete = false;
                    $answer->save();

                    continue;
                }

                $points = min((float) $input['points'], (float) $aq->points_snapshot);
                $answer->points_earned = $points;
                $answer->is_correct = $points >= $aq->points_snapshot;
                $answer->grader_notes = $input['notes'] ?? null;
                $answer->graded_by = $request->user()->id;
                $answer->graded_at = now();
                $answer->save();

                $total += $points;
            }

            // Multiple-choice points already awarded at submit time.
            $mcTotal = (float) Answer::where('attempt_id', $attempt->id)
                ->whereNotNull('attempt_question_option_id')
                ->sum('points_earned');

            $attempt->score = $mcTotal + $total;
            $attempt->max_score = $attempt->attemptQuestions()->sum('points_snapshot');
            $attempt->graded_by = $request->user()->id;
            $attempt->graded_at = now();
            $attempt->status = $complete ? 'graded' : 'grading';
            $attempt->save();
        });

        return to_route('admin.grading.show', $attempt)->with('status', 'Penilaian disimpan.');
    }
}
