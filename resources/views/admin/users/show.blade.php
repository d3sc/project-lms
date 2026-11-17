@extends('layouts.app')

@section('title', 'Detail Pengguna: {{ $user->name }}')

@section('header')
    <h1 class="page-title">Detail Pengguna</h1>
    <p class="page-subtitle">Informasi akun dan riwayat aktivitas.</p>
@endsection

@section('actions')
    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Kembali</a>
    <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary">Edit</a>
@endsection

@section('content')
<div class="grid gap-6 sm:grid-cols-2">
    <div class="card">
        <div class="card-header">
            <p class="text-sm font-medium text-slate-900">Informasi Akun</p>
        </div>
        <div class="card-body space-y-4">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Nama</dt>
                    <dd class="mt-1 text-slate-900">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Email</dt>
                    <dd class="mt-1 text-slate-600">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Peran</dt>
                    <dd class="mt-1">
                        <span class="{{ $user->isAdmin() ? 'badge-indigo' : 'badge-slate' }}">
                            {{ $user->isAdmin() ? 'Admin' : 'Siswa' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Status</dt>
                    <dd class="mt-1">
                        <span class="{{ $user->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Dibuat</dt>
                    <dd class="mt-1 text-slate-600">{{ $user->created_at?->format('d M Y H:i') }}</dd>
                </div>
                @if ($user->updated_at)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Diperbarui</dt>
                    <dd class="mt-1 text-slate-600">{{ $user->updated_at?->format('d M Y H:i') }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <p class="text-sm font-medium text-slate-900">Riwayat Ujian</p>
        </div>
        <div class="card-body p-0">
            <table class="table-base">
                <thead>
                    <tr>
                        <th class="hidden sm:table-cell">Ujian</th>
                        <th>Percobaan</th>
                        <th class="hidden sm:table-cell">Status</th>
                        <th class="hidden sm:table-cell">Nilai</th>
                        <th class="hidden sm:table-cell">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($user->examAttempts()->with('exam')->latest()->paginate(10) as $attempt)
                        <tr>
                            <td class="hidden sm:table-cell">
                                <p class="font-medium text-slate-900">{{ $attempt->exam->title }}</p>
                            </td>
                            <td class="text-center">{{ $attempt->attempt_number }}</td>
                            <td class="hidden sm:table-cell">
                                <span class="{{ $attempt->is_submitted ? 'badge-green' : 'badge-amber' }}">
                                    {{ $attempt->is_submitted ? 'Selesai' : 'Berjalan' }}
                                </span>
                            </td>
                            <td class="hidden sm:table-cell">
                                @if ($attempt->is_submitted)
                                    {{ number_format($attempt->score ?? 0, 2) }} / {{ number_format($attempt->max_score ?? 0, 2) }}
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="hidden sm:table-cell text-slate-500">
                                {{ $attempt->started_at?->format('d M Y H:i') ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-500">Belum ada riwayat ujian</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection