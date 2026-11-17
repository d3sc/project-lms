@extends('layouts.app')

@section('title', 'Hasil Ujian')

@section('header')
    <h1 class="page-title">Hasil Ujian</h1>
    <p class="page-subtitle">
        {{ $attempt->user->name }} - {{ $attempt->exam->title }}
        &middot; Percobaan #{{ $attempt->attempt_number }}
    </p>
@endsection

@section('actions')
    <a href="{{ route('student.exams.results.index') }}" class="btn-secondary btn-sm">← Kembali ke Riwayat</a>
    <a href="{{ route('student.exams.show', $attempt->exam) }}" class="btn-secondary btn-sm">Lihat Ujian Lain</a>
@endsection

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-4">
        <div class="stat-card"><span class="stat-label">Status</span><strong class="mt-1 block text-slate-900">{{ ucfirst($attempt->status) }}</strong></div>
        <div class="stat-card"><span class="stat-label">Nilai</span><strong class="mt-1 block text-slate-900">{{ number_format($attempt->score ?? 0, 2) }} / {{ number_format($attempt->max_score ?? 0, 2) }}</strong></div>
        <div class="stat-card"><span class="stat-label">Persentase</span><strong class="mt-1 block text-slate-900">{{ number_format($attempt->percentageScore(), 2) }}%</strong></div>
        <div class="stat-card"><span class="stat-label">Dikumpulkan</span><strong class="mt-1 block text-slate-900">{{ $attempt->submitted_at?->format('d M Y H:i') ?? '—' }}</strong></div>
    </div>

    @foreach ($attempt->attemptQuestions as $aq)
        @php
            $isEssay = $aq->attemptOptions->isEmpty();
            $answer = $aq->answer->first(); // HasMany -> get first
        @endphp

        <div class="card">
            <div class="card-header flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-900">Soal #{{ $loop->iteration }}</p>
                    <p class="mt-1 text-slate-700 whitespace-pre-wrap">{{ $aq->question_snapshot }}</p>
                </div>
                <span class="badge-{{ $isEssay ? 'amber' : 'indigo' }}">{{ $isEssay ? 'Esai' : 'Pilihan Ganda' }}</span>
            </div>
            <div class="card-body">
                @if ($isEssay)
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-2">Jawaban Anda</p>
                    <p class="whitespace-pre-wrap text-slate-800">{{ $answer->essay_answer ?? '(kosong)' }}</p>
                    @if ($answer?->grader_notes)
                        <p class="mt-4 text-sm text-slate-500">Catatan penilai: {{ $answer->grader_notes }}</p>
                    @endif
                @else
                    <ul class="space-y-2">
                        @foreach ($aq->attemptOptions as $opt)
                            @php
                                $selected = $answer && $answer->attempt_question_option_id === $opt->id;
                                $correct = $opt->is_correct_snapshot;
                            @endphp
                            <li class="flex items-center gap-3 p-3 rounded-lg {{ $correct ? 'bg-emerald-50 border border-emerald-200' : ($selected ? 'bg-rose-50 border border-rose-200' : 'bg-slate-50') }}">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded border {{ $correct ? 'border-emerald-500 bg-emerald-500 text-white' : ($selected ? 'border-rose-500 bg-rose-500 text-white' : 'border-slate-300 text-slate-600') }}">
                                    @if ($correct)
                                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                    @elseif ($selected)
                                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L10 10.586l-2.707-2.707a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    @endif
                                </span>
                                <span class="flex-1">{{ $opt->content_snapshot }}</span>
                                @if ($selected)
                                    <span class="text-xs font-mono text-slate-500">{{ $answer->points_earned ?? 0 }} / {{ $aq->points_snapshot }} poin</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
                <p class="mt-4 text-right font-mono text-sm text-slate-600">{{ number_format($answer->points_earned ?? 0, 2) }} / {{ number_format($aq->points_snapshot, 2) }} poin</p>
            </div>
        </div>
    @endforeach
</div>
@endsection