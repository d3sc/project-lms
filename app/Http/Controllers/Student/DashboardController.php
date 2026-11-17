<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $exams = Exam::published()
            ->where(function ($query): void {
                // Include exams that are:
                // - currently open (starts_at <= now, ends_at > now or null)
                // - scheduled for future (starts_at > now) - shown as "Segera"
                // - ongoing without end time
                $query->where(function ($q): void {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })->where(function ($q): void {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
                })
                    ->orWhere(function ($q): void {
                        $q->where('starts_at', '>', now());
                    });
            })
            ->withCount('questions')
            ->withCount(['attempts as my_attempts_count' => function ($query): void {
                $query->where('user_id', auth()->id());
            }])
            ->orderByRaw('CASE WHEN starts_at IS NULL THEN 0 WHEN starts_at <= NOW() THEN 1 ELSE 2 END')
            ->orderBy('ends_at')
            ->orderBy('title')
            ->get();

        return view('student.dashboard', compact('exams'));
    }
}
