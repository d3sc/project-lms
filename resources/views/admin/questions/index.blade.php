@extends('layouts.app')

@section('title', 'Soal Ujian')

@section('header')
    <h1 class="page-title">Soal &mdash; {{ $exam->title }}</h1>
    <p class="page-subtitle">{{ $exam->questions->count() }} soal &middot; total {{ $exam->totalPoints() }} poin</p>
@endsection

@section('actions')
    <a href="{{ route('admin.exams.questions.create', $exam) }}" class="btn-primary">Tambah Soal</a>
    <a href="{{ route('admin.exams.edit', $exam) }}" class="btn-secondary">Pengaturan Ujian</a>
@endsection

@section('content')
@if ($exam->questions->isEmpty())
    <div class="card card-body text-center py-12">
        <p class="font-medium text-slate-900">Belum ada soal</p>
        <p class="mt-1 text-sm text-slate-500">Tambahkan soal pilihan ganda atau esai untuk ujian ini.</p>
        <a href="{{ route('admin.exams.questions.create', $exam) }}" class="btn-primary mt-4 mx-auto">Tambah Soal Pertama</a>
    </div>
@else
    <div class="space-y-3">
        @foreach ($exam->questions as $i => $question)
            <div class="card">
                <div class="card-header">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-600 tabular-nums">{{ $i + 1 }}</span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="badge-{{ $question->isMultipleChoice() ? 'indigo' : 'amber' }}">
                                    {{ $question->isMultipleChoice() ? 'Pilihan Ganda' : 'Esai' }}
                                </span>
                                <span class="text-xs text-slate-500">{{ $question->points }} poin</span>
                            </div>
                            <p class="mt-1.5 line-clamp-2 text-sm text-slate-800">{{ $question->content }}</p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1.5">
                        <a href="{{ route('admin.exams.questions.edit', [$exam, $question]) }}" class="btn-ghost btn-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.exams.questions.destroy', [$exam, $question]) }}"
                              onsubmit="return confirm('Hapus soal ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-ghost btn-sm text-rose-600 hover:text-rose-700">Hapus</button>
                        </form>
                    </div>
                </div>

                @if ($question->isMultipleChoice())
                    <div class="card-body py-3">
                        <ul class="space-y-1.5">
                            @foreach ($question->options as $option)
                                <li class="flex items-start gap-2 text-sm {{ $option->is_correct ? 'font-medium text-emerald-700' : 'text-slate-600' }}">
                                    @if ($option->is_correct)
                                        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                    @else
                                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-slate-300"></span>
                                    @endif
                                    <span>{{ $option->content }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="card-body py-3 text-xs text-slate-500">
                        Jawaban esai dinilai manual oleh admin.
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
@endsection