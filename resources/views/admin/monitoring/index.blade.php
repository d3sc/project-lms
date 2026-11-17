@extends('layouts.app')

@section('title', 'Monitoring Ujian')

@section('header')
    <h1 class="page-title">Monitoring Ujian</h1>
    <p class="page-subtitle">Peserta yang sedang mengerjakan ujian. Data diperbarui otomatis setiap 5 detik.</p>
@endsection

@section('content')
<div x-data="monitoringApp()" x-init="load(); setInterval(load, 5000)" class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">Pembaruan terakhir: <span x-text="updatedAt || 'Memuat...'">Memuat...</span></p>
        <button type="button" class="btn-secondary btn-sm" @click="load()">Refresh sekarang</button>
    </div>
    <div class="card">
        <div class="card-body p-0 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50"><tr>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Peserta</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Ujian</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Percobaan</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Mulai</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Sisa waktu</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Progress</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-600">Aktivitas terakhir</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="item in items" :key="item.id">
                        <tr>
                            <td class="px-4 py-3"><div class="font-medium text-slate-900" x-text="item.user_name"></div><div class="text-xs text-slate-500" x-text="item.user_email"></div></td>
                            <td class="px-4 py-3 text-slate-700" x-text="item.exam_title"></td>
                            <td class="px-4 py-3 font-mono" x-text="'#' + item.attempt_number"></td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-600" x-text="item.started_at"></td>
                            <td class="px-4 py-3"><span class="font-mono font-medium" :class="item.remaining_seconds !== null && item.remaining_seconds < 60 ? 'text-rose-600' : 'text-emerald-700'" x-text="formatTime(item.remaining_seconds)"></span></td>
                            <td class="px-4 py-3"><span x-text="item.answered + '/' + item.total_questions"></span></td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-600" x-text="item.last_activity_at || '—'"></td>
                        </tr>
                    </template>
                    <tr x-show="!loading && items.length === 0"><td colspan="7" class="px-4 py-12 text-center text-slate-500">Tidak ada peserta yang sedang ujian.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function monitoringApp() {
    return {
        items: [], loading: false, updatedAt: '',
        async load() {
            this.loading = true;
            try {
                const response = await fetch('{{ route('admin.monitoring.feed') }}', { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Feed monitoring gagal');
                const data = await response.json();
                this.items = (data.attempts ?? []).map(a => ({
                    ...a,
                    user_name: a.student,
                    user_email: a.email,
                    exam_title: a.exam,
                    last_activity_at: a.last_activity,
                }));
                this.updatedAt = new Date().toLocaleTimeString('id-ID');
            } catch (error) { console.error(error); }
            finally { this.loading = false; }
        },
        formatTime(seconds) {
            if (seconds === null || seconds === undefined) return '∞';
            seconds = Math.max(0, Number(seconds));
            return `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
        }
    }
}
</script>
@endpush
