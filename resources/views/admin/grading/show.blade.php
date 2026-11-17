@extends('layouts.app')

@section('title', 'Penilaian Esai — {{ $attempt->exam->title }}')

@section('header')
    <h1 class="page-title">Penilaian Esai</h1>
    <p class="page-subtitle">{{ $attempt->user->name }} &middot; {{ $attempt->exam->title }} &middot; Percobaan #{{ $attempt->attempt_number }}</p>
@endsection

@section('actions')
    <a href="{{ route('admin.grading.index') }}" class="btn-secondary btn-sm">← Kembali</a>
    @if ($attempt->status === 'grading')
        <form method="POST" action="{{ route('admin.grading.store', $attempt) }}" id="grading-form-main">
            @csrf
            <button type="submit" class="btn-primary btn-sm">Simpan Penilaian</button>
        </form>
    @endif
@endsection

@section('content')
<div class="space-y-6">
    <div class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <p class="text-sm text-slate-500">Siswa: <span class="text-slate-900">{{ $attempt->user->name }}</span></p>
                <p class="text-sm text-slate-500">Ujian: <span class="text-slate-900">{{ $attempt->exam->title }}</span></p>
                <p class="text-sm text-slate-500">Percobaan: <span class="text-slate-900">{{ $attempt->attempt_number }}</span></p>
                <p class="text-sm text-slate-500">Status: <span class="badge-{{ $attempt->status === 'graded' ? 'emerald' : 'amber' }}">{{ ucfirst($attempt->status) }}</span></p>
                <p class="text-sm text-slate-500">Mulai: <span class="text-slate-900">{{ $attempt->started_at?->format('d F Y H:i') }}</span></p>
                <p class="text-sm text-slate-500">Selesai: <span class="text-slate-900">{{ $attempt->submitted_at?->format('d F Y H:i') }}</span></p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.grading.store', $attempt) }}" id="grading-form-main">
            @csrf
            <input type="hidden" name="_method" value="POST">
            
            @foreach ($attempt->attemptQuestions as $aq)
                @php
                    $isEssay = $aq->attemptOptions->isEmpty(); // Essay = no options
                    $answer = $aq->answer;
                @endphp

                <div class="card">
                    <div class="card-header flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900">Soal #{{ $loop->iteration }} ({{ $aq->points_snapshot }} poin)</p>
                            <p class="mt-1 text-slate-700 whitespace-pre-wrap">{{ $aq->question_snapshot }}</p>
                            @if ($aq->explanation_snapshot)
                                <p class="mt-2 text-xs text-slate-500">{{ $aq->explanation_snapshot }}</p>
                            @endif
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <span class="badge-{{ $isEssay ? 'amber' : 'indigo' }}">{{ $isEssay ? 'Esai' : 'Pilihan Ganda' }}</span>
                        </div>
                    </div>

                    @if (!$isEssay)
                        <div class="card-body">
                            <ul class="space-y-2">
                                @foreach ($aq->attemptOptions as $opt)
                                    @php
                                        $selected = $answer && $answer->attempt_question_option_id === $opt->id;
                                        $correct = $opt->is_correct_snapshot;
                                        $earned = $selected ? ($answer->points_earned ?? 0) : 0;
                                    @endphp
                                    <li class="flex items-center gap-3 p-3 rounded-lg {{ $correct ? 'bg-emerald-50 border border-emerald-200' : ($selected ? 'bg-rose-50 border border-rose-200' : 'bg-slate-50') }}">
                                        <span class="grid h-6 w-6 shrink-0 place-items-center rounded border {{ $correct ? 'border-emerald-500 bg-emerald-500 text-white' : ($selected ? 'border-rose-500 bg-rose-500 text-white' : 'border-slate-300 text-slate-600') }}">
                                            @if ($correct)
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                            @else
                                                <span class="h-3.5 w-3.5 shrink-0 rounded border {{ $selected ? 'border-rose-500 bg-rose-500 text-white' : 'border-slate-300 text-slate-600' }}"></span>
                                            @endif
                                        </span>
                                        <span class="flex-1">{{ $opt->content_snapshot }}</span>
                                        @if ($selected)
                                            <span class="text-xs font-mono text-slate-500">{{ $earned }} / {{ $aq->points_snapshot }} poin</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="card-body space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Jawaban Siswa</label>
                                <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 min-h-[100px] whitespace-pre-wrap text-slate-900">{{ $answer->essay_answer ?? '(kosong)' }}</div>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="points_{{ $aq->id }}" class="block text-sm font-medium text-slate-700 mb-1">Poin (maks {{ $aq->points_snapshot }})</label>
                                    <input type="number" name="answers[{{ $aq->id }}][points]" id="points_{{ $aq->id }}"
                                        value="{{ $answer->points_earned ?? '' }}"
                                        min="0" max="{{ $aq->points_snapshot }}" step="0.1"
                                        class="form-input w-full" required>
                                </div>
                                <div>
                                    <label for="notes_{{ $aq->id }}" class="block text-sm font-medium text-slate-700 mb-1">Catatan Penilai (opsional)</label>
                                    <textarea name="answers[{{ $aq->id }}][notes]" id="notes_{{ $aq->id }}"
                                        rows="3" class="form-input w-full">{{ $answer->grader_notes ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('admin.grading.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" form="grading-form-main" class="btn-primary">Simpan Semua Penilaian</button>
            </div>
        </form>
    </div>
</div>
@endsection