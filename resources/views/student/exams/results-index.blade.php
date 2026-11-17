@extends('layouts.app')

@section('title', 'Riwayat Ujian')

@section('header')
    <h1 class="page-title">Riwayat Ujian</h1>
    <p class="page-subtitle">Daftar percobaan ujian yang pernah Anda ikuti.</p>
@endsection

@section('actions')
    <a href="{{ route('student.dashboard') }}" class="btn-secondary">Kembali ke Dashboard</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body p-0">
        @if ($attempts->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-slate-500">Belum ada riwayat ujian.</p>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($attempts as $attempt)
                    <a href="{{ in_array($attempt->status, ['submitted', 'grading', 'graded'], true) ? route('student.exams.results', $attempt) : route('student.exams.attempt', $attempt) }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-slate-50">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $attempt->exam->title }}</p>
                            <p class="mt-1 text-xs text-slate-500">Percobaan #{{ $attempt->attempt_number }} &middot; {{ $attempt->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="badge-{{ $attempt->status === 'graded' ? 'green' : ($attempt->status === 'in_progress' ? 'indigo' : 'amber') }}">{{ ucfirst($attempt->status) }}</span>
                            @if ($attempt->status === 'graded')
                                <p class="mt-1 text-xs font-semibold text-slate-700">{{ $attempt->score }} / {{ $attempt->max_score }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $attempts->links() }}</div>
        @endif
    </div>
</div>
@endsection
