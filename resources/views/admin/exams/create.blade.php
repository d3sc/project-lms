@extends('layouts.app')

@section('title', 'Buat Ujian')

@section('header')
    <h1 class="page-title">Buat Ujian Baru</h1>
    <p class="page-subtitle">Isi informasi dasar ujian. Soal bisa ditambahkan setelah disimpan.</p>
@endsection

@section('actions')
    <a href="{{ route('admin.exams.index') }}" class="btn-secondary">Kembali</a>
@endsection

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.exams.store') }}" class="fieldset card card-body">
        @csrf

        <div>
            <label for="title" class="form-label">Judul Ujian <span class="text-rose-500">*</span></label>
            <input type="text" id="title" name="title" class="form-input" required autofocus
                   value="{{ old('title') }}" placeholder="Contoh: Ujian Tengah Semester Matematika">
        </div>

        <div>
            <label for="description" class="form-label">Deskripsi</label>
            <textarea id="description" name="description" rows="3" class="form-input"
                      placeholder="Deskripsi singkat ujian (opsional)">{{ old('description') }}</textarea>
            <p class="form-hint">Ditampilkan pada halaman detail ujian sebelum siswa memulai.</p>
        </div>

        <div>
            <label for="instructions" class="form-label">Petunjuk Ujian</label>
            <textarea id="instructions" name="instructions" rows="4" class="form-input"
                      placeholder="Petunjuk untuk peserta (opsional)">{{ old('instructions') }}</textarea>
            <p class="form-hint">Ditampilkan sebelum ujian dimulai.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="duration_minutes" class="form-label">Durasi (menit)</label>
                <input type="number" id="duration_minutes" name="duration_minutes" class="form-input" min="1" max="6000"
                       placeholder="Kosongkan untuk tanpa batas"
                       value="{{ old('duration_minutes', 60) }}">
                <p class="form-hint">Kosongkan bila ujian tidak dibatasi waktu.</p>
            </div>
            <div>
                <label for="max_attempts" class="form-label">Maksimum Percobaan <span class="text-rose-500">*</span></label>
                <input type="number" id="max_attempts" name="max_attempts" class="form-input" min="1" max="20"
                       value="{{ old('max_attempts', 1) }}" required>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="starts_at" class="form-label">Mulai Berlaku</label>
                <input type="datetime-local" id="starts_at" name="starts_at" class="form-input"
                       value="{{ old('starts_at') ? \Carbon\Carbon::parse(old('starts_at'))->format('Y-m-d\\TH:i') : '' }}">
                <p class="form-hint">Kosongkan untuk segera setelah dipublikasikan.</p>
            </div>
            <div>
                <label for="ends_at" class="form-label">Berakhir</label>
                <input type="datetime-local" id="ends_at" name="ends_at" class="form-input"
                       value="{{ old('ends_at') ? \Carbon\Carbon::parse(old('ends_at'))->format('Y-m-d\\TH:i') : '' }}">
                <p class="form-hint">Kosongkan untuk tanpa batas waktu.</p>
            </div>
        </div>

        <div class="space-y-3 border-t border-slate-100 pt-4">
            <div class="flex items-start gap-3">
                <input type="checkbox" id="randomize_questions" name="randomize_questions"
                       class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                       {{ old('randomize_questions', true) ? 'checked' : '' }}>
                <label for="randomize_questions" class="text-sm text-slate-700 cursor-pointer">
                    Acak urutan soal tiap percobaan
                </label>
            </div>
            <div class="flex items-start gap-3">
                <input type="checkbox" id="randomize_options" name="randomize_options"
                       class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                       {{ old('randomize_options', true) ? 'checked' : '' }}>
                <label for="randomize_options" class="text-sm text-slate-700 cursor-pointer">
                    Acak urutan opsi jawaban pilihan ganda
                </label>
            </div>
            <div class="flex items-start gap-3">
                <input type="checkbox" id="show_result" name="show_result"
                       class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                       {{ old('show_result', true) ? 'checked' : '' }}>
                <label for="show_result" class="text-sm text-slate-700 cursor-pointer">
                    Tampilkan hasil ke siswa setelah dinilai
                </label>
            </div>
            <div class="flex items-start gap-3">
                <input type="checkbox" id="show_correct_answers" name="show_correct_answers"
                       class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                       {{ old('show_correct_answers') ? 'checked' : '' }}>
                <label for="show_correct_answers" class="text-sm text-slate-700 cursor-pointer">
                    Tampilkan kunci jawaban (saat show_result aktif)
                </label>
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
            <a href="{{ route('admin.exams.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection