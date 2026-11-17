@extends('layouts.app')

@section('title', 'Kelola Ujian')

@section('header')
    <h1 class="page-title">Ujian</h1>
    <p class="page-subtitle">Daftar semua ujian. Klik untuk menambah soal atau mengubah pengaturan.</p>
@endsection

@section('actions')
    <a href="{{ route('admin.exams.create') }}" class="btn-primary">Buat Ujian</a>
@endsection

@section('content')
<div class="card overflow-hidden">
    <div class="card-body p-0">
        <table class="table-base">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th class="hidden sm:table-cell">Status</th>
                    <th class="hidden md:table-cell">Durasi</th>
                    <th class="hidden md:table-cell">Percobaan Max</th>
                    <th class="hidden sm:table-cell">Soal</th>
                    <th class="hidden lg:table-cell">Peserta</th>
                    <th class="w-36 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($exams as $exam)
                    <tr>
                        <td class="max-w-xs">
                            <p class="truncate font-medium text-slate-900">{{ $exam->title }}</p>
                            @if ($exam->description)
                                <p class="truncate text-xs text-slate-500">{{ Str::limit($exam->description, 80) }}</p>
                            @endif
                        </td>
                        <td class="hidden sm:table-cell">
                            @if ($exam->isPublished())
                                <span class="badge-green">Dipublikasikan</span>
                            @else
                                <span class="badge-slate">Draft</span>
                            @endif
                        </td>
                        <td class="hidden md:table-cell text-center tabular-nums">{{ $exam->durationLabel() }}</td>
                        <td class="hidden md:table-cell text-center tabular-nums">{{ $exam->max_attempts }}</td>
                        <td class="hidden sm:table-cell text-center tabular-nums">{{ $exam->questions_count }}</td>
                        <td class="hidden lg:table-cell text-center tabular-nums">{{ $exam->attempts_count }}</td>
                        <td class="w-36 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.exams.edit', $exam) }}" class="btn-ghost btn-sm" title="Edit">Edit</a>
                                @if ($exam->isPublished())
                                    <form method="POST" action="{{ route('admin.exams.unpublish', $exam) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-ghost btn-sm text-amber-600 hover:text-amber-700" title="Batalkan Publikasi">Unpub</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.exams.publish', $exam) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-ghost btn-sm text-emerald-600 hover:text-emerald-700" title="Publikasikan">Publish</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-slate-500">
                            <p class="font-medium">Belum ada ujian</p>
                            <a href="{{ route('admin.exams.create') }}" class="mt-1 inline-block text-xs text-indigo-600 hover:underline">Buat ujian pertama</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $exams->links() }}
</div>
@endsection