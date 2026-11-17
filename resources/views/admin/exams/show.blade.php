@extends('layouts.app')

@section('title', $exam->title)

@section('header')
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="page-title">{{ $exam->title }}</h1>
            <p class="page-subtitle">Ringkasan ujian dan daftar soal.</p>
        </div>
        <a href="{{ route('admin.exams.edit', $exam) }}" class="btn-primary">Edit Ujian</a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card p-5"><p class="text-xs font-medium uppercase text-slate-500">Durasi</p><p class="mt-2 text-2xl font-semibold">{{ $exam->durationLabel() }}</p></div>
        <div class="card p-5"><p class="text-xs font-medium uppercase text-slate-500">Maks. percobaan</p><p class="mt-2 text-2xl font-semibold">{{ $exam->max_attempts }}</p></div>
        <div class="card p-5"><p class="text-xs font-medium uppercase text-slate-500">Jumlah soal</p><p class="mt-2 text-2xl font-semibold">{{ $exam->questions->count() }}</p></div>
        <div class="card p-5"><p class="text-xs font-medium uppercase text-slate-500">Status</p><p class="mt-2 text-lg font-semibold">{{ $exam->isPublished() ? 'Dipublikasikan' : 'Draft' }}</p></div>
    </section>

    <section class="card overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-semibold text-slate-900">Soal ujian</h2></div>
        <div class="divide-y divide-slate-100">
            @forelse ($exam->questions as $question)
                <div class="flex items-start justify-between gap-4 px-5 py-4">
                    <div><p class="text-sm font-medium text-slate-900">{{ $loop->iteration }}. {{ $question->content }}</p><p class="mt-1 text-xs text-slate-500">{{ $question->type === 'essay' ? 'Esai' : 'Pilihan ganda' }} · {{ $question->points }} poin</p></div>
                    <a href="{{ route('admin.exams.questions.edit', [$exam, $question]) }}" class="btn-ghost btn-sm">Edit</a>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-slate-500">Belum ada soal.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('form').forEach(form => form.addEventListener('submit', () => form.querySelectorAll('button[type=submit]').forEach(button => button.disabled = true));
</script>
@endpush
