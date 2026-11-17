{{-- resources/views/admin/partials/_form.blade.php --}}
@extends('layouts.app')

@props(['action', 'method', 'question'])

<div class="card">
    <div class="card-header">
        <h2 class="text-sm font-semibold text-slate-900">
            @if ($question)
                Edit Soal
            @else
                Tambah Soal
            @endif
        </h2>
    </div>
    <div class="card-body space-y-6">
        <form method="{{ $method }}" action="{{ $action }}" class="space-y-6">
            @if ($question)
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Soal Lama</h2>
                    <p class="text-xs text-slate-500">{{ $question->content }}</p>
                </div>
            @endif

            <div>
                <label for="type" class="label-base">Tipe Soal</label>
                <select id="type" name="type" class="input-base w-full mt-1" onchange="toggleOptions()">
                    <option value="multiple_choice" {{ $question->type === 'multiple_choice' ? 'selected' : '' }}>Pilihan Ganda</option>
                    <option value="essay" {{ $question->type === 'essay' ? 'selected' : '' }}>Esai</option>
                </select>
            </div>

            <div>
                <label for="content" class="label-base">Pertanyaan <span class="text-red-500">*</span></label>
                <textarea id="content" name="content" rows="4" class="input-base w-full mt-1" required>{{ $question->content ?? '' }}</textarea>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="points" class="label-base">Bobot (poin) <span class="text-red-500">*</span></label>
                    <input type="number" id="points" name="points" min="1" value="{{ $question->points ?? 1 }}" class="input-base w-full mt-1" required>
                </div>
                <div>
                    <label for="order" class="label-base">Urutan</label>
                    <input type="number" id="order" name="order" min="1" value="{{ $question->order ?? ($exam->questions->count() + 1) }}" class="input-base w-full mt-1">
                </div>
            </div>
        </div>

        <div id="options-section" class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-slate-900">Opsi Jawaban (Pilihan Ganda)</h2>
                <p class="text-xs text-slate-500">Minimal 2 opsi, satu jawaban benar.</p>
            </div>
            <div class="card-body space-y-4">
                <div id="options-container">
                    @for ($i = 1; $i <= 4; $i++)
                        <div class="flex items-start gap-3">
                            <input type="radio" name="correct_option" value="{{ $i }}" class="mt-1.5 text-indigo-600 focus:ring-indigo-500" {{ ($question->options->firstWhere('is_correct', true) ?? collect($options)->first(fn($o) => $o['order'] == $i)) ? 'checked' : '' }}>
                            <div class="flex-1">
                                <label class="label-base">Opsi {{ $i }} <span class="text-red-500">*</span></label>
                                <input type="text" name="options[{{ $i }}][content]" class="input-base w-full mt-1" value="{{ $question->options->first(fn($o) => $o['order'] == $i) ? $o['content'] : '' }}">
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn-secondary btn-sm" onclick="addOption()">+ Tambah Opsi</button>
                <input type="hidden" name="option_count" id="option_count" value="4">
            </div>
        </div>

        <div id="essay-section" class="hidden card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-slate-900">Pengaturan Esai</h2>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label for="max_words" class="label-base">Batas Kata Maksimal</label>
                    <input type="number" id="max_words" name="max_words" min="0" class="input-base w-full mt-1">
                    <p class="mt-1 text-xs text-slate-500">0 = tidak dibatasi.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <button type="submit" class="btn-primary">Simpan</button>
            <a href="{{ route('admin.exams.questions.index', $exam) }}" class="btn-secondary">Batal</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
let optionIndex = 5;
function toggleOptions() {
    const type = document.getElementById('type').value;
    const optionsSection = document.getElementById('options-section');
    const essaySection = document.getElementById('essay-section');
    if (type === 'multiple_choice') {
        optionsSection.classList.remove('hidden');
        essaySection.classList.add('hidden');
    } else {
        optionsSection.classList.add('hidden');
        essaySection.classList.remove('hidden');
    }
}
function addOption() {
    const container = document.getElementById('options-container');
    const div = document.createElement('div');
    div.className = 'flex items-start gap-3';
    div.innerHTML = `
        <input type="radio" name="correct_option" value="${optionIndex}" class="mt-1.5 text-indigo-600 focus:ring-indigo-500">
        <div class="flex-1">
            <label class="label-base">Opsi ${optionIndex} <span class="text-red-500">*</span></label>
            <input type="text" name="options[${optionIndex}][content]" class="input-base w-full mt-1" required>
        </div>
    `;
    container.appendChild(div);
    document.getElementById('option_count').value = optionIndex;
    optionIndex++;
}
</script>
@endpush