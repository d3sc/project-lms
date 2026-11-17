@extends('layouts.app')

@section('title', 'Hasil Ujian')

@section('header')
    <h1 class="page-title">Hasil Ujian</h1>
    <p class="page-subtitle">Pantau nilai dan status seluruh percobaan siswa.</p>
@endsection

@section('actions')
    <a href="{{ route('admin.results.export', request()->query()) }}" class="btn-secondary btn-sm">Export CSV</a>
@endsection

@section('content')
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" class="grid gap-4 md:grid-cols-3 md:items-end">
            <div>
                <label for="exam_id" class="form-label">Ujian</label>
                <select id="exam_id" name="exam_id" class="form-input w-full">
                    <option value="">Semua ujian</option>
                    @foreach ($exams as $exam)
                        <option value="{{ $exam->id }}" @selected(request('exam_id') == $exam->id)>{{ $exam->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-input w-full">
                    <option value="">Semua status</option>
                    @foreach (['submitted' => 'Terkumpul', 'grading' => 'Perlu dinilai', 'graded' => 'Selesai dinilai'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn-primary">Terapkan Filter</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Siswa</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Ujian</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Percobaan</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-600">Nilai</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Dikumpulkan</th>
                    <th class="px-4 py-3 text-right font-medium text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($attempts as $attempt)
                    <tr>
                        <td class="px-4 py-3"><div class="font-medium text-slate-900">{{ $attempt->user->name }}</div><div class="text-xs text-slate-500">{{ $attempt->user->email }}</div></td>
                        <td class="px-4 py-3 text-slate-700">{{ $attempt->exam->title }}</td>
                        <td class="px-4 py-3 font-mono">#{{ $attempt->attempt_number }}</td>
                        <td class="px-4 py-3"><span class="badge-{{ $attempt->status === 'graded' ? 'emerald' : ($attempt->status === 'grading' ? 'amber' : 'indigo') }}">{{ ['submitted' => 'Terkumpul', 'grading' => 'Perlu dinilai', 'graded' => 'Selesai'][$attempt->status] ?? $attempt->status }}</span></td>
                        <td class="px-4 py-3 text-right font-mono">{{ $attempt->score !== null ? number_format($attempt->score, 2) : '—' }} / {{ number_format($attempt->max_score, 2) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $attempt->submitted_at?->format('d M Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.results.show', $attempt) }}" class="btn-secondary btn-sm">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">Belum ada hasil ujian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $attempts->links() }}</div>
@endsection