@extends('layouts.app')

@section('title', 'Konflik Jadwal - MeetSpace')

@section('content')
    <x-admin-nav />

    <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Rekomendasi Konflik SAW</h1>
            <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">Perbandingan objektif untuk jadwal yang saling bertabrakan. Keputusan baru diterapkan setelah konfirmasi admin.</p>
        </div>
        <span class="w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">{{ count($groups) }} kelompok konflik</span>
    </div>

    <div class="space-y-6">
        @forelse($groups as $group)
            @php($winner = $group['analysis']['winner'])
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <header class="flex flex-col justify-between gap-3 border-b border-slate-200 p-5 dark:border-slate-700 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-white">{{ $group['room']->name }}</h2>
                        <p class="mt-1 text-xs text-slate-500"><i class="fas fa-calendar-day mr-1"></i>{{ $group['date']->translatedFormat('l, d F Y') }} · {{ $group['bookings']->count() }} jadwal bertabrakan</p>
                    </div>
                    <form method="POST" action="{{ route('admin.conflicts.apply', $group['bookings']->first()) }}" onsubmit="return confirm('Terapkan rekomendasi? Jadwal lain dalam kelompok ini akan dibatalkan.')">
                        @csrf
                        <button class="rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700"><i class="fas fa-check-double mr-2"></i>Terapkan Rekomendasi</button>
                    </form>
                </header>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left dark:divide-slate-700">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                            <tr><th class="px-5 py-3">Peringkat</th><th class="px-5 py-3">Agenda</th><th class="px-5 py-3">Waktu</th><th class="px-5 py-3">Divisi</th><th class="px-5 py-3">Skor SAW</th><th class="px-5 py-3">Status</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80">
                            @foreach($group['analysis']['results'] as $result)
                                <tr @class(['bg-emerald-50/70 dark:bg-emerald-950/20' => $result['rank'] === 1])>
                                    <td class="px-5 py-4"><span @class(['inline-flex h-8 w-8 items-center justify-center rounded-full text-xs font-black', 'bg-emerald-600 text-white' => $result['rank'] === 1, 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' => $result['rank'] !== 1])>#{{ $result['rank'] }}</span></td>
                                    <td class="px-5 py-4"><div class="font-bold text-slate-900 dark:text-white">{{ $result['booking']->title }}</div><div class="mt-1 text-xs text-slate-500">{{ $result['booking']->user_name ?: $result['booking']->user?->name }} · {{ $result['booking']->attendees_count }} peserta</div></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm">{{ substr($result['booking']->start_time, 0, 5) }}–{{ substr($result['booking']->end_time, 0, 5) }}</td>
                                    <td class="px-5 py-4 text-sm">{{ $result['booking']->user_dept ?: 'Internal' }}</td>
                                    <td class="px-5 py-4"><div class="text-lg font-black text-blue-600 dark:text-blue-400">{{ number_format($result['preference_score'], 4) }}</div><details class="mt-1 text-xs text-slate-500"><summary class="cursor-pointer">Lihat matriks</summary><div class="mt-2 whitespace-nowrap">@foreach($result['raw'] as $key => $raw)<span class="mr-3 uppercase">{{ $key }}: {{ $raw['value'] }}</span>@endforeach</div></details></td>
                                    <td class="px-5 py-4"><x-booking-status :status="$result['booking']->status" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <footer class="border-t border-slate-100 bg-slate-50 px-5 py-3 text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-900/40">
                    Rekomendasi: <strong class="text-slate-800 dark:text-slate-200">{{ $winner['booking']->title }}</strong>. Bobot: kepentingan 30%, peserta 20%, durasi 15%, waktu pengajuan 20%, frekuensi divisi 15%.
                </footer>
            </section>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-800">
                <i class="fas fa-circle-check text-4xl text-emerald-500"></i>
                <h2 class="mt-4 font-bold text-slate-900 dark:text-white">Tidak ada konflik aktif</h2>
                <p class="mt-1 text-sm text-slate-500">Semua pengajuan saat ini dapat diproses tanpa perbandingan SAW.</p>
            </div>
        @endforelse
    </div>
@endsection
