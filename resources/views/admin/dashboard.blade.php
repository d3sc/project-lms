@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('header')
    <h1 class="page-title">Dashboard Admin</h1>
    <p class="page-subtitle">Ringkasan aktivitas ujian, penilaian, dan manajemen pengguna.</p>
@endsection

@section('actions')
    <a href="{{ route('admin.exams.create') }}" class="btn-primary">Buat Ujian</a>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Quick Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Total Siswa</p>
                    <p class="stat-value">{{ number_format($stats['students']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Akun terdaftar</p>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-indigo-50">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Total Ujian</p>
                    <p class="stat-value">{{ number_format($stats['exams']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $stats['published'] }} dipublikasikan</p>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-emerald-50">
                    <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Sedang Mengerjakan</p>
                    <p class="stat-value text-indigo-600">{{ number_format($stats['in_progress']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">
                        <a href="{{ route('admin.monitoring.index') }}" class="text-indigo-600 hover:underline">Lihat monitoring</a>
                    </p>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-indigo-50">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Menunggu Penilaian</p>
                    <p class="stat-value {{ $stats['pending_grading'] > 0 ? 'text-amber-600' : 'text-slate-500' }}">{{ number_format($stats['pending_grading']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">
                        <a href="{{ route('admin.grading.index') }}" class="text-indigo-600 hover:underline">Nilai esai</a>
                    </p>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-amber-50">
                    <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <a href="{{ route('admin.exams.index') }}" class="card card-body group hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-lg bg-indigo-100 group-hover:bg-indigo-200 transition-colors">
                    <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-900 group-hover:text-indigo-600 transition-colors">Kelola Ujian</p>
                    <p class="text-xs text-slate-500">Buat, edit, publikasikan ujian</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.users.index') }}" class="card card-body group hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-lg bg-emerald-100 group-hover:bg-emerald-200 transition-colors">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-900 group-hover:text-emerald-600 transition-colors">Kelola Pengguna</p>
                    <p class="text-xs text-slate-500">Tambah akun siswa, atur status</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.results.index') }}" class="card card-body group hover:shadow-md transition-shadow">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-items-center rounded-lg bg-amber-100 group-hover:bg-amber-200 transition-colors">
                    <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-900 group-hover:text-amber-600 transition-colors">Hasil & Ekspor</p>
                    <p class="text-xs text-slate-500">Lihat rekap nilai, unduh CSV</p>
                </div>
            </div>
        </a>
    </div>

    {{-- Activity Panels --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Active Attempts --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-slate-900">Peserta Sedang Ujian</h2>
                <a href="{{ route('admin.monitoring.index') }}" class="text-xs font-medium text-indigo-600 hover:underline">Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse ($activeAttempts as $attempt)
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3 last:border-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $attempt->user->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $attempt->exam->title }} &middot; #{{ $attempt->attempt_number }}</p>
                        </div>
                        <span class="badge-indigo tabular-nums shrink-0">
                            {{ $attempt->isTimeLimited() ? gmdate('i:s', $attempt->remainingSeconds()) : '∞' }}
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-slate-500 text-center">Tidak ada peserta yang sedang mengerjakan ujian.</p>
                @endforelse
            </div>
        </div>

        {{-- Pending Essay Grading --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-slate-900">Esai Menunggu Penilaian</h2>
                <a href="{{ route('admin.grading.index') }}" class="text-xs font-medium text-indigo-600 hover:underline">Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse ($pendingEssays as $attempt)
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3 last:border-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $attempt->user->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $attempt->exam->title }}</p>
                        </div>
                        <a href="{{ route('admin.grading.show', $attempt) }}" class="btn-secondary btn-sm">Nilai</a>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-slate-500 text-center">Tidak ada esai yang menunggu penilaian.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Exams --}}
    <div class="card">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-slate-900">Ujian Terbaru</h2>
            <a href="{{ route('admin.exams.index') }}" class="text-xs font-medium text-indigo-600 hover:underline">Lihat semua</a>
        </div>
        <div class="card-body p-0">
            <table class="table-base">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th class="hidden sm:table-cell">Status</th>
                        <th class="hidden md:table-cell">Durasi</th>
                        <th class="hidden lg:table-cell">Soal</th>
                        <th class="hidden lg:table-cell">Peserta</th>
                        <th class="w-36 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentExams as $exam)
                        <tr>
                            <td class="max-w-xs">
                                <p class="truncate font-medium text-slate-900">{{ $exam->title }}</p>
                            </td>
                            <td class="hidden sm:table-cell">
                                @if ($exam->isPublished())
                                    <span class="badge-green">Dipublikasikan</span>
                                @else
                                    <span class="badge-slate">Draft</span>
                                @endif
                            </td>
                            <td class="hidden md:table-cell text-center tabular-nums">{{ $exam->durationLabel() }}</td>
                            <td class="hidden lg:table-cell text-center tabular-nums">{{ $exam->questions_count }}</td>
                            <td class="hidden lg:table-cell text-center tabular-nums">{{ $exam->attempts_count }}</td>
                            <td class="w-36 text-right">
                                <a href="{{ route('admin.exams.edit', $exam) }}" class="btn-ghost btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                                <p class="font-medium">Belum ada ujian</p>
                                <a href="{{ route('admin.exams.create') }}" class="mt-1 inline-block text-xs text-indigo-600 hover:underline">Buat ujian pertama</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection