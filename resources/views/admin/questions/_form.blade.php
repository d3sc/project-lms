@php
    $isEdit = isset($question) && $question;
    $options = $isEdit ? $question->options->sortBy('order')->values() : collect();
    $correctId = $isEdit ? $question->options->firstWhere('is_correct', true)?->id : null;
    $letters = range('A', 'H');
@endphp

<form method="POST" action="{{ $action }}" class="max-w-3xl space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="card">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-slate-900">Informasi Soal</h2>
        </div>
        <div class="card-body space-y-4">
            <div>
                <label for="type" class="label-base">Tipe Soal</label>
                <select id="type" name="type" class="input-base mt-1 w-full" onchange="toggleQuestionType()">
                    <option value="multiple_choice" @selected(old('type', $question->type ?? 'multiple_choice') === 'multiple_choice')>Pilihan Ganda</option>
                    <option value="essay" @selected(old('type', $question->type ?? '') === 'essay')>Esai</option>
                </select>
                @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="content" class="label-base">Pertanyaan <span class="text-red-500">*</span></label>
                <textarea id="content" name="content" rows="4" class="input-base mt-1 w-full" required>{{ old('content', $question->content ?? '') }}</textarea>
                @error('content') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="points" class="label-base">Bobot (poin) <span class="text-red-500">*</span></label>
                    <input type="number" id="points" name="points" min="1" max="100" value="{{ old('points', $question->points ?? 1) }}" class="input-base mt-1 w-full" required>
                    @error('points') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="order" class="label-base">Urutan</label>
                    <input type="number" id="order" name="order" min="1" value="{{ old('order', $question->order ?? ($exam->questions->count() + 1)) }}" class="input-base mt-1 w-full">
                </div>
            </div>
        </div>
    </div>

    <div id="options-section" class="card">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Opsi Jawaban</h2>
                <p class="text-xs text-slate-500">Minimal 2 opsi. Pilih satu jawaban benar.</p>
            </div>
        </div>
        <div class="card-body space-y-3">
            <div id="options-container" class="space-y-3">
                @for ($i = 1; $i <= 4; $i++)
                    @php($opt = $options->get($i - 1))
                    <div class="flex items-start gap-3">
                        <label class="mt-2 flex items-center gap-2 text-xs font-semibold text-slate-500">
                            <input type="radio" name="correct_option" value="{{ $i }}"
                                   class="text-indigo-600 focus:ring-indigo-500"
                                   @checked((int) old('correct_option', $isEdit && $opt && $opt->id === $correctId ? $i : ($i === 1 && ! $isEdit ? 1 : 0)) === $i)>
                            {{ $letters[$i - 1] }}
                        </label>
                        <input type="text" name="options[{{ $i }}][content]" value="{{ old("options.$i.content", $opt->content ?? '') }}"
                               class="input-base w-full" placeholder="Isi opsi {{ $letters[$i - 1] }}">
                    </div>
                @endfor
            </div>
            @error('options') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @error('correct_option') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div id="essay-section" class="card hidden">
        <div class="card-body">
            <p class="text-sm text-slate-600">Soal esai dinilai manual oleh admin. Tidak ada opsi jawaban yang perlu diisi.</p>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.exams.questions.index', $exam) }}" class="btn-secondary">Batal</a>
        <button type="submit" class="btn-primary">Simpan Soal</button>
    </div>
</form>

@push('scripts')
<script>
function toggleQuestionType() {
    const type = document.getElementById('type').value;
    document.getElementById('options-section').classList.toggle('hidden', type !== 'multiple_choice');
    document.getElementById('essay-section').classList.toggle('hidden', type !== 'essay');
}
document.addEventListener('DOMContentLoaded', toggleQuestionType);
</script>
@endpush
