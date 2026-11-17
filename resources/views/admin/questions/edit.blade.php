@extends('layouts.app')

@section('title', 'Edit Soal')

@section('header')
    <h1 class="page-title">Edit Soal</h1>
    <p class="page-subtitle">Ujian: {{ $exam->title }}</p>
@endsection

@section('actions')
    <a href="{{ route('admin.exams.questions.index', $exam) }}" class="btn-secondary">Kembali</a>
@endsection

@section('content')
    @include('admin.questions._form', [
        'action' => route('admin.exams.questions.update', [$exam, $question]),
        'method' => 'PUT',
        'question' => $question,
        'exam' => $exam,
    ])
@endsection