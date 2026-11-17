<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'students' => User::where('role', 'student')->count(),
            'exams' => Exam::count(),
            'published' => Exam::published()->count(),
            'questions' => Question::count(),
            'in_progress' => ExamAttempt::where('status', 'in_progress')->count(),
            'pending_grading' => ExamAttempt::where('status', 'grading')->count(),
            'completed' => ExamAttempt::whereIn('status', ['graded', 'submitted'])->count(),
        ];

        $activeAttempts = ExamAttempt::with(['user', 'exam'])
            ->where('status', 'in_progress')
            ->orderByDesc('started_at')
            ->limit(5)
            ->get();

        $pendingEssays = ExamAttempt::with(['user', 'exam'])
            ->where('status', 'grading')
            ->orderBy('submitted_at')
            ->limit(5)
            ->get();

        $recentExams = Exam::withCount(['questions', 'attempts'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'activeAttempts', 'pendingEssays', 'recentExams'));
    }
}
