@extends('layouts.app')

@section('title', 'Persetujuan Booking - MeetSpace')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Persetujuan Peminjaman</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tinjau dan putuskan pengajuan pemakaian ruangan.</p>
    </div>

    <form method="GET" class="mb-6 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:grid-cols-12">
        <input name="search" value="{{ $filters['search'] }}" placeholder="Cari agenda, pemesan, atau ruangan" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 sm:col-span-7">
        <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 sm:col-span-3">
            <option value="">Semua Status</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700 sm:col-span-2"><i class="fas fa-search mr-1"></i>Cari</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left dark:divide-slate-700">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                    <tr><th class="px-5 py-4">Agenda</th><th class="px-5 py-4">Pemesan</th><th class="px-5 py-4">Jadwal</th><th class="px-5 py-4">Status</th><th class="px-5 py-4 text-right">Tindakan</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80">
                    @forelse($bookings as $booking)
                        <tr class="align-top hover:bg-slate-50/70 dark:hover:bg-slate-700/20">
                            <td class="px-5 py-4"><div class="font-bold text-slate-900 dark:text-white">{{ $booking->title }}</div><div class="mt-1 text-xs text-slate-500">{{ $booking->room->name }} · {{ $booking->attendees_count }} peserta</div></td>
                            <td class="px-5 py-4"><div class="text-sm font-semibold">{{ $booking->user_name ?: $booking->user->name }}</div><div class="mt-1 text-xs text-slate-500">{{ $booking->user_dept ?: 'Internal' }}</div></td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-600 dark:text-slate-300"><div>{{ $booking->date->translatedFormat('d M Y') }}</div><div class="mt-1">{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</div></td>
                            <td class="px-5 py-4"><x-booking-status :status="$booking->status" /></td>
                            <td class="px-5 py-4">
                                @if($booking->status->value === 'pending')
                                    <div class="flex justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">@csrf<button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700"><i class="fas fa-check mr-1"></i>Setujui</button></form>
                                        <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}" onsubmit="return confirm('Tolak pengajuan ini?')">@csrf<button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-300"><i class="fas fa-times mr-1"></i>Tolak</button></form>
                                    </div>
                                @else
                                    <div class="text-right text-xs text-slate-400">Sudah diproses</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-14 text-center text-sm text-slate-500">Tidak ada data yang sesuai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $bookings->links() }}</div>
@endsection
