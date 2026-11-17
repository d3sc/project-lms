<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $exams = Exam::withCount(['questions', 'attempts'])
            ->latest()
            ->paginate(15);

        return view('admin.exams.index', compact('exams'));
    }

    public function create(): View
    {
        return view('admin.exams.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        $exam = Exam::create($data);

        return to_route('admin.exams.edit', $exam)->with('status', 'Ujian berhasil dibuat. Tambahkan soal sekarang.');
    }

    public function show(Exam $exam): View
    {
        $exam->load(['questions.options', 'creator']);

        return view('admin.exams.show', compact('exam'));
    }

    public function edit(Exam $exam): View
    {
        $exam->load(['questions' => fn ($q) => $q->withCount('options')]);

        return view('admin.exams.edit', compact('exam'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $exam->update($this->validated($request));

        return to_route('admin.exams.edit', $exam)->with('status', 'Ujian berhasil diperbarui.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return to_route('admin.exams.index')->with('status', 'Ujian berhasil dihapus.');
    }

    public function publish(Exam $exam): RedirectResponse
    {
        if ($exam->questions()->count() === 0) {
            return back()->withErrors(['publish' => 'Ujian tanpa soal tidak dapat dipublikasikan.']);
        }

        $exam->update(['published_at' => $exam->published_at ?? now()]);

        return back()->with('status', 'Ujian dipublikasikan.');
    }

    public function unpublish(Exam $exam): RedirectResponse
    {
        $exam->update(['published_at' => null]);

        return back()->with('status', 'Publikasi ujian dibatalkan.');
    }

    protected function validated(Request $request): array
    {
        $input = $request->all();
        foreach (['duration_minutes', 'starts_at', 'ends_at'] as $field) {
            if (empty($input[$field])) {
                $input[$field] = null;
            }
        }
        foreach (['randomize_questions', 'randomize_options', 'show_result', 'show_correct_answers'] as $field) {
            $input[$field] = $request->boolean($field);
        }
        $request->merge($input);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:6000'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => array_filter([
                'nullable',
                'date',
                $request->filled('starts_at') ? 'after_or_equal:starts_at' : null,
            ]),
            'randomize_questions' => ['sometimes', 'boolean'],
            'randomize_options' => ['sometimes', 'boolean'],
            'show_result' => ['sometimes', 'boolean'],
            'show_correct_answers' => ['sometimes', 'boolean'],
        ], [
            'ends_at.after_or_equal' => 'Waktu selesai harus sama atau setelah waktu mulai.',
        ]);

        foreach (['randomize_questions', 'randomize_options', 'show_result', 'show_correct_answers'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        return $data;
    }
}
