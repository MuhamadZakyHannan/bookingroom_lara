@extends('layouts.app')

@section('title', 'Kalender Ruangan - MeetSpace')

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Kalender Ruangan</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tampilan bulanan seluruh agenda yang telah disetujui.</p>
        </div>
        <label class="text-xs font-bold text-slate-500">
            Filter Ruangan
            <select id="calendarRoomFilter" class="mt-1 block min-w-64 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                <option value="0">Semua Ruangan</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" @selected($selectedRoomId === $room->id)>{{ $room->code }} — {{ $room->name }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-6">
        <div id="meetingCalendar" data-events-url="{{ route('calendar.events') }}" data-room-id="{{ $selectedRoomId }}"></div>
    </div>

    <dialog id="calendarEventDialog" class="w-[min(92vw,32rem)] rounded-2xl bg-white p-0 text-slate-800 shadow-2xl backdrop:bg-slate-900/60 dark:bg-slate-800 dark:text-slate-100">
        <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-700">
            <div class="flex items-start justify-between gap-4">
                <div><div class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Detail Rapat</div><h2 data-event-title class="mt-1 text-xl font-bold"></h2></div>
                <button type="button" data-dialog-close class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <dl class="space-y-4 p-6 text-sm">
            <div><dt class="text-xs font-bold uppercase text-slate-400">Waktu</dt><dd data-event-time class="mt-1 font-semibold"></dd></div>
            <div><dt class="text-xs font-bold uppercase text-slate-400">Ruangan</dt><dd data-event-room class="mt-1 font-semibold"></dd></div>
            <div><dt class="text-xs font-bold uppercase text-slate-400">Pemesan</dt><dd data-event-organizer class="mt-1 font-semibold"></dd></div>
            <div><dt class="text-xs font-bold uppercase text-slate-400">Tujuan / Catatan</dt><dd data-event-purpose class="mt-1 text-slate-600 dark:text-slate-300"></dd></div>
        </dl>
        <div class="flex justify-end border-t border-slate-100 bg-slate-50 px-6 py-4 dark:border-slate-700 dark:bg-slate-900/40"><button type="button" data-dialog-close class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white">Selesai</button></div>
    </dialog>
@endsection
