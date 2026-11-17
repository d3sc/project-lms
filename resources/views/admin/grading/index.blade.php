@extends('layouts.app')

@section('title', 'Penilaian Esai')

@section('header')
    <h1 class="page-title">Penilaian Esai</h1>
    <p class="page-subtitle">Ujian yang menunggu penilaian manual jawaban esai.</p>
@endsection

@section('content')
@if ($attempts->isEmpty())
    <div class="card card-body py-12 text-center">
        <p class="font-medium text-slate-900">Tidak ada yang menunggu penilaian</p>
        <p class="mt-1 text-sm text-slate-500">Attempt dengan jawaban esai akan muncul di sini setelah siswa mengumpulkan.</p>
    </div>
@else
    <div class="card">
        <div class="card-body p-0 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">Siswa</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">Ujian</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">Percobaan</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600">Dikumpulkan</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($attempts as $attempt)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $attempt->user->name }}</div>
                                <div class="text-xs text-slate-500">{{ $attempt->user->email }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $attempt->exam->title }}</td>
                            <td class="px-4 py-3 font-mono text-slate-700">#{{ $attempt->attempt_number }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $attempt->submitted_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.grading.show', $attempt) }}" class="btn-primary btn-sm">Nilai</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $attempts->links() }}</div>
@endif
@endsection