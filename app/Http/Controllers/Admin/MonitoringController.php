<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(): View
    {
        return view('admin.monitoring.index');
    }

    /**
     * JSON feed polled by the admin dashboard every 5 seconds.
     */
    public function feed(Request $request, ExamAttemptService $service): JsonResponse
    {
        // Server-authoritative: expire anything past its deadline first.
        $service->autoSubmitExpired();

        $attempts = ExamAttempt::with(['user:id,name,email', 'exam:id,title'])
            ->where('status', 'in_progress')
            ->orderBy('started_at')
            ->get()
            ->map(function (ExamAttempt $a) {
                $answered = $a->answers()->whereNotNull('attempt_question_option_id')->count()
                    + $a->answers()->whereNotNull('essay_answer')->count();

                return [
                    'id' => $a->id,
                    'student' => $a->user->name,
                    'email' => $a->user->email,
                    'exam' => $a->exam->title,
                    'attempt_number' => $a->attempt_number,
                    'started_at' => $a->started_at?->toIso8601String(),
                    'remaining_seconds' => $a->remainingSeconds(),
                    'answered' => $answered,
                    'total_questions' => $a->attemptQuestions()->count(),
                    'last_activity' => $a->answers()->max('updated_at'),
                ];
            });

        return response()->json([
            'server_time' => now()->toIso8601String(),
            'count' => $attempts->count(),
            'attempts' => $attempts,
        ]);
    }
}
