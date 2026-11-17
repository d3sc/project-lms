<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResultController extends Controller
{
    public function index(Request $request): View
    {
        $query = ExamAttempt::with(['user', 'exam'])
            ->whereIn('status', ['submitted', 'grading', 'graded'])
            ->when($request->integer('exam_id'), fn ($q, $id) => $q->where('exam_id', $id))
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->latest('submitted_at');

        $attempts = $query->paginate(25)->withQueryString();

        $exams = Exam::orderBy('title')->get(['id', 'title']);

        return view('admin.results.index', compact('attempts', 'exams'));
    }

    public function show(ExamAttempt $attempt): View
    {
        abort_unless(in_array($attempt->status, ['submitted', 'grading', 'graded'], true), 404);

        $attempt->load([
            'user',
            'exam',
            'attemptQuestions.attemptOptions',
            'attemptQuestions.answer',
        ]);

        return view('admin.results.show', compact('attempt'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = ExamAttempt::with(['user', 'exam'])
            ->whereIn('status', ['submitted', 'grading', 'graded'])
            ->when($request->integer('exam_id'), fn ($q, $id) => $q->where('exam_id', $id))
            ->latest('submitted_at');

        $filename = 'exam-results-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'attempt_id', 'exam', 'student', 'email', 'attempt_number',
                'status', 'score', 'max_score', 'percentage',
                'started_at', 'submitted_at', 'graded_at',
            ]);

            $query->chunk(200, function ($attempts) use ($out) {
                foreach ($attempts as $a) {
                    fputcsv($out, [
                        $a->id,
                        $a->exam->title,
                        $a->user->name,
                        $a->user->email,
                        $a->attempt_number,
                        $a->status,
                        $a->score,
                        $a->max_score,
                        $a->percentageScore(),
                        $a->started_at?->format('Y-m-d H:i:s'),
                        $a->submitted_at?->format('Y-m-d H:i:s'),
                        $a->graded_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
