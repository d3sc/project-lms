@extends('layouts.app')

@section('title', 'Detail Hasil Ujian')

@section('header')
    <h1 class="page-title">Detail Hasil Ujian</h1>
    <p class="page-subtitle">{{ $attempt->user->name }} &middot; {{ $attempt->exam->title }} &middot; Percobaan #{{ $attempt->attempt_number }}</p>
@endsection

@section('actions')
    <a href="{{ route('admin.results.index') }}" class="btn-secondary btn-sm">← Kembali</a>
@endsection

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-4">
        <div class="stat-card"><span class="stat-label">Siswa</span><strong class="mt-1 block text-slate-900">{{ $attempt->user->name }}</strong></div>
        <div class="stat-card"><span class="stat-label">Status</span><strong class="mt-1 block text-slate-900">{{ ucfirst($attempt->status) }}</strong></div>
        <div class="stat-card"><span class="stat-label">Nilai</span><strong class="mt-1 block text-slate-900">{{ $attempt->score !== null ? number_format($attempt->score, 2) : '—' }} / {{ number_format($attempt->max_score, 2) }}</strong></div>
        <div class="stat-card"><span class="stat-label">Persentase</span><strong class="mt-1 block text-slate-900">{{ number_format($attempt->percentageScore(), 2) }}%</strong></div>
    </div>

    @foreach ($attempt->attemptQuestions as $aq)
        @php $answer = $aq->answer; $isEssay = $aq->attemptOptions->isEmpty(); @endphp
        <div class="card">
            <div class="card-header flex items-start justify-between gap-4">
                <div><p class="text-sm font-medium text-slate-900">Soal #{{ $loop->iteration }}</p><p class="mt-1 whitespace-pre-wrap text-slate-700">{{ $aq->question_snapshot }}</p></div>
                <span class="badge-{{ $isEssay ? 'amber' : 'indigo' }}">{{ $isEssay ? 'Esai' : 'Pilihan Ganda' }}</span>
            </div>
            <div class="card-body">
                @if ($isEssay)
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Jawaban siswa</p>
                    <p class="mt-2 whitespace-pre-wrap text-slate-800">{{ $answer->essay_answer ?? '(kosong)' }}</p>
                    @if ($answer?->grader_notes)<p class="mt-4 text-sm text-slate-500">Catatan: {{ $answer->grader_notes }}</p>@endif
                @else
                    <p class="text-sm text-slate-700">Jawaban: <strong>{{ $answer?->attemptOption?->content_snapshot ?? '(tidak dijawab)' }}</strong></p>
                @endif
                <p class="mt-4 text-right font-mono text-sm text-slate-600">{{ number_format($answer?->points_earned ?? 0, 2) }} / {{ number_format($aq->points_snapshot, 2) }} poin</p>
            </div>
        </div>
    @endforeach
</div>
@endsection
