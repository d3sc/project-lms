@extends('layouts.app')

@section('title', 'Edit Ujian')

@section('header')
    <h1 class="page-title">{{ $exam->title }}</h1>
    <p class="page-subtitle">
        <span class="badge-{{ $exam->isPublished() ? 'green' : 'slate' }}">{{ $exam->isPublished() ? 'Dipublikasikan' : 'Draft' }}</span>
        &middot; {{ $exam->questions->count() }} soal &middot; {{ $exam->durationLabel() }}
    </p>
@endsection

@section('actions')
    <a href="{{ route('admin.exams.questions.create', $exam) }}" class="btn-primary">Tambah Soal</a>
    <a href="{{ route('admin.exams.questions.index', $exam) }}" class="btn-secondary">Kelola Soal</a>
@endsection

@section('content')
<div class="space-y-6">

    <form method="POST" action="{{ route('admin.exams.update', $exam) }}" class="fieldset card card-body max-w-2xl">
        @csrf
        @method('PUT')

        <h2 class="text-sm font-semibold text-slate-900">Pengaturan Ujian</h2>

        <div>
            <label for="title" class="form-label">Judul Ujian <span class="text-rose-500">*</span></label>
            <input type="text" id="title" name="title" class="form-input" required
                   value="{{ old('title', $exam->title) }}">
        </div>

        <div>
            <label for="description" class="form-label">Deskripsi</label>
            <textarea id="description" name="description" rows="3" class="form-input">{{ old('description', $exam->description) }}</textarea>
        </div>

        <div>
            <label for="instructions" class="form-label">Petunjuk Ujian</label>
            <textarea id="instructions" name="instructions" rows="4" class="form-input">{{ old('instructions', $exam->instructions) }}</textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="duration_minutes" class="form-label">Durasi (menit)</label>
                <input type="number" id="duration_minutes" name="duration_minutes" class="form-input" min="1" max="6000" placeholder="Kosongkan untuk tanpa batas" value="{{ old('duration_minutes', $exam->duration_minutes) }}">
                <p class="form-hint">Kosongkan bila ujian tidak dibatasi waktu.</p>
            </div>
            <div>
                <label for="max_attempts" class="form-label">Maksimum Percobaan <span class="text-rose-500">*</span></label>
                <input type="number" id="max_attempts" name="max_attempts" class="form-input" min="1" max="20"
                       value="{{ old('max_attempts', $exam->max_attempts) }}" required>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="starts_at" class="form-label">Mulai Berlaku</label>
                <input type="datetime-local" id="starts_at" name="starts_at" class="form-input"
                       value="{{ old('starts_at', $exam->starts_at?->format('Y-m-d\\TH:i')) }}">
            </div>
            <div>
                <label for="ends_at" class="form-label">Berakhir</label>
                <input type="datetime-local" id="ends_at" name="ends_at" class="form-input"
                       value="{{ old('ends_at', $exam->ends_at?->format('Y-m-d\\TH:i')) }}">
            </div>
        </div>

        <div class="space-y-3 border-t border-slate-100 pt-4">
            @foreach ([
                'randomize_questions' => 'Acak urutan soal tiap percobaan',
                'randomize_options' => 'Acak urutan opsi jawaban',
                'show_result' => 'Tampilkan hasil ke siswa',
                'show_correct_answers' => 'Tampilkan kunci jawaban ke siswa',
            ] as $field => $label)
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1"
                           class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                           {{ old($field, $exam->{$field}) ? 'checked' : '' }}>
                    <label for="{{ $field }}" class="cursor-pointer text-sm text-slate-700">{{ $label }}</label>
                </div>
            @endforeach
        </div>

        <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>

    <div class="card max-w-2xl border-rose-200">
        <div class="card-header border-rose-100">
            <div>
                <h2 class="text-sm font-semibold text-rose-700">Hapus Ujian</h2>
                <p class="mt-0.5 text-xs text-slate-500">Seluruh soal dan riwayat percobaan terkait juga akan dihapus.</p>
            </div>
            <form method="POST" action="{{ route('admin.exams.destroy', $exam) }}"
                  onsubmit="return confirm('Hapus ujian ini beserta seluruh soalnya? Tindakan ini tidak bisa dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger btn-sm">Hapus</button>
            </form>
        </div>
    </div>
</div>
@endsection