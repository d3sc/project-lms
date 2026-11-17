<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\GradingController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ExamController as StudentExamController;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Compatibility endpoint used by Breeze authentication flows.
Route::middleware('auth')->get('/dashboard', function () {
    return auth()->user()->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('student.dashboard');
})->name('dashboard');

// Authentication (Breeze)
require __DIR__.'/auth.php';

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Users
    Route::resource('users', UserController::class);

    // Exams
    Route::resource('exams', ExamController::class);
    Route::post('exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');
    Route::post('exams/{exam}/unpublish', [ExamController::class, 'unpublish'])->name('exams.unpublish');

    // Questions
    Route::prefix('exams/{exam}/questions')->name('exams.questions.')->group(function () {
        Route::get('/', [QuestionController::class, 'index'])->name('index');
        Route::post('/', [QuestionController::class, 'store'])->name('store');
        Route::get('/create', [QuestionController::class, 'create'])->name('create');
        Route::put('/{question}', [QuestionController::class, 'update'])->name('update');
        Route::delete('/{question}', [QuestionController::class, 'destroy'])->name('destroy');
        Route::get('/{question}/edit', [QuestionController::class, 'edit'])->name('edit');
    });

    // Monitoring
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/feed', [MonitoringController::class, 'feed'])->name('monitoring.feed');
    Route::get('/monitoring/data', [MonitoringController::class, 'feed'])->name('monitoring.data');

    // Grading
    Route::get('/grading', [GradingController::class, 'index'])->name('grading.index');
    Route::get('/grading/{attempt}', [GradingController::class, 'show'])->name('grading.show');
    Route::post('/grading/{attempt}', [GradingController::class, 'grade'])->name('grading.store');

    // Results
    Route::get('/results/export', [ResultController::class, 'export'])->name('results.export');
    Route::get('/results', [ResultController::class, 'index'])->name('results.index');
    Route::get('/results/{attempt}', [ResultController::class, 'show'])->name('results.show');
});

// Student routes
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');

    // The dashboard is the available-exams index; keep a conventional URL/name for navigation and integrations.
    Route::get('/exams', StudentDashboardController::class)->name('exams.index');
    Route::get('/exams/{exam}', [StudentExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{exam}/start', [StudentExamController::class, 'start'])->name('exams.start');

    Route::get('/attempts/{attempt}', [StudentExamController::class, 'attempt'])->name('exams.attempt');
    Route::post('/attempts/{attempt}/answer', [StudentExamController::class, 'saveAnswer'])->name('exams.answer');
    Route::post('/attempts/{attempt}/submit', [StudentExamController::class, 'submit'])->name('exams.submit');
    Route::get('/attempts/{attempt}/results', [StudentExamController::class, 'results'])->name('exams.results');

    Route::get('/results', function () {
        $attempts = ExamAttempt::with(['exam'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('student.exams.results-index', compact('attempts'));
    })->name('exams.results.index');
});

// Profile routes (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
