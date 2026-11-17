@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('header')
    <h1 class="page-title">Halo, {{ auth()->user()->name }}</h1>
    <p class="page-subtitle">Ujian yang tersedia untuk Anda ikuti.</p>
@endsection

@section('actions')
    <a href="{{ route('student.exams.results.index') }}" class="btn-secondary">Riwayat Ujian</a>
@endsection

@section('content')
<div class="space-y-6">
    @if ($exams->isEmpty())
        <div class="card card-body text-center py-12">
            <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-xl bg-slate-100">
                <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-slate-900">Belum ada ujian tersedia</h2>
            <p class="mt-1 text-sm text-slate-500">Saat ini tidak ada ujian yang dipublikasikan atau dalam jadwal.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($exams as $exam)
                @php
                    $attemptsUsed = $exam->my_attempts_count;
                    $canAttempt = $attemptsUsed < $exam->max_attempts;
                    $isUpcoming = $exam->starts_at && $exam->starts_at->isFuture();
                    $isOngoing = !$isUpcoming && ($exam->ends_at ? !$exam->ends_at->isPast() : true);
                @endphp
                <article class="card group hover:shadow-md transition-shadow">
                    <div class="card-body">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate font-semibold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $exam->title }}</h3>
                                @if ($exam->description)
                                    <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $exam->description }}</p>
                                @endif
                            </div>
                            @if ($isUpcoming)
                                <span class="badge-slate shrink-0">Segera</span>
                            @elseif (!$canAttempt)
                                <span class="badge-red shrink-0">Habis</span>
                            @elseif ($isOngoing)
                                <span class="badge-green shrink-0">Terbuka</span>
                            @else
                                <span class="badge-amber shrink-0">Tertutup</span>
                            @endif
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                            <span class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                {{ $exam->questions_count }} soal
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $exam->durationLabel() }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $attemptsUsed }}/{{ $exam->max_attempts }} percobaan
                            </span>
                        </div>

                        @if ($exam->randomize_questions || $exam->randomize_options)
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @if ($exam->randomize_questions)
                                    <span class="badge-slate">Soal diacak</span>
                                @endif
                                @if ($exam->randomize_options)
                                    <span class="badge-slate">Opsi diacak</span>
                                @endif
                            </div>
                        @endif

                        <div class="mt-4 flex items-center gap-2">
                            <a href="{{ route('student.exams.show', $exam) }}"
                               class="{{ $canAttempt && $isOngoing ? 'btn-primary' : 'btn-secondary' }} btn-sm flex-1 text-center"
                               {{ !($canAttempt && $isOngoing) ? 'aria-disabled="true" tabindex="-1"' : '' }}>
                                {{ $isUpcoming ? 'Belum dibuka' : ($canAttempt ? ($isOngoing ? 'Mulai Ujian' : 'Ujian tertutup') : 'Percobaan habis') }}
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection