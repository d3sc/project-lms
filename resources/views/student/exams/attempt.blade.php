@extends('layouts.app')

@section('title')Mengerjakan: {{ $attempt->exam->title }}@endsection

@section('header')
    <h1 class="page-title">Mengerjakan: {{ $attempt->exam->title }}</h1>
    <p class="page-subtitle">
        Percobaan #{{ $attempt->attempt_number }} &middot;
        @if ($attempt->isTimeLimited())
            Waktu tersisa: <span id="timer" class="font-mono text-rose-600">—</span>
        @else
            <span class="font-medium text-emerald-600">Tanpa batas waktu</span>
        @endif
    </p>
@endsection

@section('content')
<div id="exam-app" class="space-y-6" x-data="examApp()" x-init="init()">
    <div class="card" x-show="currentQuestion !== null" x-transition>
        <div class="card-header flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-medium text-slate-900" x-text="'Soal ' + (currentIndex + 1) + ' dari ' + totalQuestions"></p>
                <p class="mt-1 text-slate-700 whitespace-pre-wrap" x-text="currentQuestion.question_snapshot"></p>
            </div>
            <span class="badge-indigo shrink-0" x-text="currentQuestion.attemptOptions.length ? 'Pilihan Ganda' : 'Esai'"></span>
        </div>
        <div class="card-body" x-show="currentQuestion.attemptOptions.length">
            <ul class="space-y-2">
                <template x-for="(opt, i) in currentQuestion.attemptOptions" :key="opt.id">
                    <li class="flex items-center gap-3 p-3 rounded-lg"
                        :class="{
                            'bg-indigo-50 border border-indigo-200': selectedId === opt.id,
                            'bg-slate-50': selectedId !== opt.id
                        }">
                        <label class="flex items-center gap-3 w-full cursor-pointer">
                            <input type="radio" name="answer" :value="opt.id" x-model="selectedId" x-on:change="saveAnswer(opt.id)">
                            <span class="grid h-5 w-5 shrink-0 place-items-center rounded border border-slate-300">
                                <span x-show="opt.id === selectedId" class="h-2.5 w-2.5 rounded-full bg-indigo-600"></span>
                            </span>
                            <span class="flex-1" x-text="opt.content_snapshot"></span>
                        </label>
                    </li>
                </template>
            </ul>
        </div>
        <div class="card-body" x-show="!currentQuestion.attemptOptions.length">
            <label class="block text-sm font-medium text-slate-700 mb-2">Jawaban Anda</label>
            <textarea x-model="essayAnswer" rows="8" class="form-input w-full" placeholder="Tulis jawaban esai di sini..." x-on:input.debounce.1000="saveEssay()"></textarea>
            <p class="mt-2 text-xs text-slate-500">Jawaban tersimpan otomatis.</p>
        </div>
    </div>

    <div class="card" x-show="totalQuestions > 1">
        <div class="card-header"><p class="text-sm font-medium text-slate-900">Navigasi Soal</p></div>
        <div class="card-body p-4">
            <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12 gap-2" role="tablist">
                <template x-for="(q, i) in questions" :key="q.id">
                    <button type="button" role="tab"
                        :class="{
                            'bg-indigo-600 text-white': currentIndex === i,
                            'bg-emerald-100 text-emerald-800 border-emerald-300': answeredIndices.includes(i) && currentIndex !== i,
                            'bg-rose-100 text-rose-800 border-rose-300': isEssay(q) && essayAnswers[q.id] && currentIndex !== i,
                            'bg-slate-100 text-slate-700 border-slate-300': !answeredIndices.includes(i) && !isEssay(q) && currentIndex !== i,
                        }"
                        class="px-3 py-2 rounded-lg border font-medium text-sm transition-colors"
                        x-on:click="goToQuestion(i)"
                        x-text="i + 1"></button>
                </template>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center gap-4">
        <button type="button" class="btn-secondary" x-on:click="goToPrev()" :disabled="currentIndex === 0" x-show="totalQuestions > 1">← Sebelumnya</button>
        <div class="flex-1"></div>
        <button type="button" class="btn-primary" x-on:click="goToNext()" :disabled="currentIndex >= totalQuestions - 1" x-show="totalQuestions > 1">Selanjutnya →</button>
        <button type="button" class="btn-primary" x-on:click="confirmSubmit()" x-show="!submitted">Kumpulkan Ujian</button>
        <span class="text-sm text-slate-500" x-show="submitted">Ujian telah dikumpulkan</span>
    </div>

    <div x-show="submitting" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 text-center">
            <h2 class="text-lg font-semibold text-slate-900">Yakin ingin mengumpulkan?</h2>
            <p class="mt-2 text-slate-600">Anda tidak bisa mengubah jawaban setelah dikumpulkan.</p>
            <div class="mt-6 flex gap-3 justify-center">
                <button type="button" class="btn-secondary" x-on:click="submitting = false">Batal</button>
                <button type="button" class="btn-danger" x-on:click="submit()">Ya, Kumpulkan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $questionsPayload = $attempt->attemptQuestions->values()->map(fn ($q) => [
        'id' => $q->id,
        'question_snapshot' => $q->question_snapshot,
        'attemptOptions' => $q->attemptOptions->map(fn ($o) => [
            'id' => $o->id,
            'content_snapshot' => $o->content_snapshot,
        ])->values()->all(),
    ])->all();

    $essayPayload = $attempt->answers->whereNotNull('essay_answer')
        ->pluck('essay_answer', 'attempt_question_id')->all();

    $optionPayload = $attempt->answers->whereNotNull('attempt_question_option_id')
        ->pluck('attempt_question_option_id', 'attempt_question_id')->all();

    $indexOf = $attempt->attemptQuestions->values()->pluck('id')->flip();
    $answeredIndices = $attempt->answers->whereNotNull('attempt_question_option_id')
        ->pluck('attempt_question_id')
        ->map(fn ($id) => $indexOf[$id] ?? null)
        ->filter(fn ($i) => $i !== null)->values()->all();
@endphp
<script>
function examApp() {
    return {
        questions: @json($questionsPayload),
        totalQuestions: @json($attempt->attemptQuestions->count()),
        remainingSeconds: @json($attempt->remainingSeconds()),
        currentIndex: 0,
        selectedId: null,
        essayAnswers: @json($essayPayload),
        answeredOptions: @json($optionPayload),
        answeredIndices: @json($answeredIndices),
        submitting: false,
        submitted: false,
        timerInterval: null,

        init() {
            this.goToQuestion(this.firstUnansweredIndex());
            if (this.remainingSeconds !== null) {
                this.updateTimerDisplay();
                this.startTimer();
            }
        },

        firstUnansweredIndex() {
            const idx = this.questions.findIndex((q, i) => !this.answeredIndices.includes(i) && !this.essayAnswers[q.id]);
            return idx === -1 ? 0 : idx;
        },

        get currentQuestion() { return this.questions[this.currentIndex]; },
        isEssay(q) { return !q.attemptOptions.length; },

        startTimer() {
            this.timerInterval = setInterval(() => {
                this.remainingSeconds--;
                this.updateTimerDisplay();
                if (this.remainingSeconds <= 0) {
                    clearInterval(this.timerInterval);
                    this.autoSubmit();
                }
            }, 1000);
        },

        updateTimerDisplay() {
            const m = Math.floor(this.remainingSeconds / 60);
            const s = this.remainingSeconds % 60;
            const el = document.getElementById('timer');
            if (el) el.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            if (this.remainingSeconds < 60) el?.classList.add('animate-pulse');
        },

        async saveAnswer(optionId) {
            const q = this.currentQuestion;
            const fd = new FormData();
            fd.append('attempt_question_id', q.id);
            fd.append('option_id', optionId);
            await fetch('{{ route("student.exams.answer", $attempt) }}', { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } });
            this.answeredOptions[q.id] = optionId;
            this.answeredIndices = [...new Set([...this.answeredIndices, this.currentIndex])];
        },

        async saveEssay() {
            const q = this.currentQuestion;
            const fd = new FormData();
            fd.append('attempt_question_id', q.id);
            fd.append('essay_answer', this.essayAnswer);
            await fetch('{{ route("student.exams.answer", $attempt) }}', { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } });
            this.essayAnswers[q.id] = this.essayAnswer;
        },

        goToQuestion(i) { this.currentIndex = i; this.updateCurrentAnswer(); },
        goToPrev() { if (this.currentIndex > 0) this.goToQuestion(this.currentIndex - 1); },
        goToNext() { if (this.currentIndex < this.totalQuestions - 1) this.goToQuestion(this.currentIndex + 1); },

        updateCurrentAnswer() {
            const q = this.currentQuestion;
            if (this.isEssay(q)) {
                this.essayAnswer = this.essayAnswers[q.id] ?? '';
            } else {
                const savedOptionId = this.answeredOptions[q.id] ?? null;
                this.selectedId = savedOptionId !== null && q.attemptOptions.some(o => o.id === savedOptionId)
                    ? savedOptionId
                    : null;
            }
        },

        confirmSubmit() { this.submitting = true; },

        async submit() {
            const res = await fetch('{{ route("student.exams.submit", $attempt) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } });
            if (res.ok) { this.submitted = true; window.location.href = '{{ route("student.exams.results", $attempt) }}'; }
            else { alert('Gagal mengumpulkan'); this.submitting = false; }
        },

        async autoSubmit() {
            if (this.submitted) return;
            const res = await fetch('{{ route("student.exams.submit", $attempt) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } });
            if (res.ok) window.location.href = '{{ route("student.exams.results", $attempt) }}';
        }
    };
}
</script>
@endpush