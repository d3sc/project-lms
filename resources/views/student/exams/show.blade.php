@extends('layouts.app')

@section('title', $exam->title)

@section('header')
    <h1 class="page-title">{{ $exam->title }}</h1>
    <p class="page-subtitle">Persiapkan diri sebelum memulai ujian.</p>
@endsection

@section('actions')
    <a href="{{ route('student.dashboard') }}" class="btn-secondary">Dashboard</a>
    <a href="{{ route('student.exams.results.index') }}" class="btn-secondary">Riwayat Hasil</a>
@endsection

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="card">
        <div class="card-body space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">{{ $exam->title }}</h2>
                @if ($exam->description)
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $exam->description }}</p>
                @endif
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">Jumlah soal</p>
                    <p class="mt-1 text-xl font-semibold text-slate-900">{{ $exam->questions_count }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">Durasi</p>
                    <p class="mt-1 text-xl font-semibold text-slate-900">{{ $exam->durationLabel() }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">Maks. percobaan</p>
                    <p class="mt-1 text-xl font-semibold text-slate-900">{{ $exam->max_attempts }}</p>
                </div>
            </div>

            @if ($exam->instructions)
                <div class="border-t border-slate-100 pt-5">
                    <h3 class="text-sm font-semibold text-slate-900">Instruksi</h3>
                    <div class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $exam->instructions }}</div>
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5">
                <div class="text-xs text-slate-500">
                    @if ($exam->randomize_questions)
                        Urutan soal diacak per percobaan.
                    @endif
                    @if ($exam->randomize_options)
                        Urutan opsi diacak per percobaan.
                    @endif
                </div>
                @if ($exam->isOpen())
                    <form method="POST" action="{{ route('student.exams.start', $exam) }}" onsubmit="return confirm('Mulai ujian sekarang? Timer akan langsung berjalan.');">
                        @csrf
                        <button type="submit" class="btn-primary">Mulai Ujian</button>
                    </form>
                @elseif ($exam->starts_at && $exam->starts_at->isFuture())
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Ujian dimulai pada {{ $exam->starts_at->translatedFormat('d M Y H:i') }}
                    </div>
                @else
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Ujian sudah ditutup.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // The server remains authoritative for eligibility and attempt creation.
</script>
@endpush