<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Exam $exam): View
    {
        $exam->load(['questions' => fn ($q) => $q->with('options')->withCount('options')]);

        return view('admin.questions.index', compact('exam'));
    }

    public function create(Exam $exam): View
    {
        return view('admin.questions.create', compact('exam'));
    }

    public function store(Request $request, Exam $exam): RedirectResponse
    {
        $this->saveQuestion($request, $exam);

        return to_route('admin.exams.questions.index', $exam)
            ->with('status', 'Soal berhasil ditambahkan.');
    }

    public function edit(Exam $exam, Question $question): View
    {
        $this->ensureBelongs($exam, $question);

        return view('admin.questions.edit', compact('exam', 'question'));
    }

    public function update(Request $request, Exam $exam, Question $question): RedirectResponse
    {
        $this->ensureBelongs($exam, $question);
        $this->saveQuestion($request, $exam, $question);

        return to_route('admin.exams.questions.index', $exam)
            ->with('status', 'Soal berhasil diperbarui.');
    }

    public function destroy(Exam $exam, Question $question): RedirectResponse
    {
        $this->ensureBelongs($exam, $question);
        $question->delete();

        return to_route('admin.exams.questions.index', $exam)
            ->with('status', 'Soal berhasil dihapus.');
    }

    protected function saveQuestion(Request $request, Exam $exam, ?Question $question = null): void
    {
        $data = $request->validate([
            'type' => ['required', 'in:multiple_choice,essay'],
            'content' => ['required', 'string', 'max:5000'],
            'explanation' => ['nullable', 'string', 'max:2000'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'options' => ['array'],
            'options.*.content' => ['nullable', 'string', 'max:2000'],
            'correct_option' => ['nullable', 'integer'],
        ]);

        $isMc = $data['type'] === 'multiple_choice';
        $options = $data['options'] ?? [];

        if ($isMc) {
            $contentful = collect($options)->filter(fn ($o) => filled($o['content'] ?? null));

            if ($contentful->count() < 2) {
                throw ValidationException::withMessages([
                    'options' => 'Soal pilihan ganda minimal membutuhkan 2 opsi jawaban.',
                ]);
            }

            if ($request->input('correct_option') === null || ! $contentful->has((int) $request->input('correct_option'))) {
                throw ValidationException::withMessages([
                    'correct_option' => 'Tentukan satu jawaban benar.',
                ]);
            }
        }

        DB::transaction(function () use ($data, $exam, $question, $isMc, $options, $request) {
            $order = $question?->order ?? ($exam->questions()->max('order') + 1);

            $payload = [
                'exam_id' => $exam->id,
                'type' => $data['type'],
                'content' => $data['content'],
                'explanation' => $data['explanation'] ?? null,
                'points' => $data['points'],
                'order' => $order,
            ];

            if ($question) {
                $question->update($payload);
            } else {
                $question = Question::create($payload);
            }

            $question->options()->delete();

            if ($isMc) {
                $correct = (int) $request->input('correct_option');
                $position = 1;

                foreach ($options as $index => $option) {
                    if (blank($option['content'] ?? null)) {
                        continue;
                    }

                    $question->options()->create([
                        'content' => $option['content'],
                        'is_correct' => $index === $correct,
                        'order' => $position++,
                    ]);
                }
            }
        });
    }

    protected function ensureBelongs(Exam $exam, Question $question): void
    {
        abort_unless($question->exam_id === $exam->id, 404);
    }
}
