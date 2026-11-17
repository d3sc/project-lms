@extends('layouts.app')

@section('title', 'Tambah Soal')

@section('header')
    <h1 class="page-title">Tambah Soal</h1>
    <p class="page-subtitle">Ujian: {{ $exam->title }}</p>
@endsection

@section('actions')
    <a href="{{ route('admin.exams.questions.index', $exam) }}" class="btn-secondary">Kembali</a>
@endsection

@section('content')
    @include('admin.questions._form', [
        'action' => route('admin.exams.questions.store', $exam),
        'method' => 'POST',
        'question' => null,
        'exam' => $exam,
    ])
@endsection
