@extends('layouts.app')

@section('title', 'Booking Saya - MeetSpace')

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Booking Saya</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pantau status pengajuan dan jadwal rapat Anda.</p>
        </div>
        <a href="{{ route('bookings.create') }}" class="inline-flex w-fit items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-700"><i class="fas fa-plus"></i>Booking Baru</a>
    </div>

    <div class="space-y-4">
        @forelse($bookings as $booking)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <x-booking-status :status="$booking->status" />
                        </div>
                        <h2 class="truncate text-lg font-bold text-slate-900 dark:text-white">{{ $booking->title }}</h2>
                        <div class="mt-2 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500 dark:text-slate-400">
                            <span><i class="fas fa-door-open mr-1 text-blue-500"></i>{{ $booking->room->name }}</span>
                            <span><i class="fas fa-calendar mr-1"></i>{{ $booking->date->translatedFormat('d M Y') }}</span>
                            <span><i class="fas fa-clock mr-1"></i>{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }} WIB</span>
                            <span><i class="fas fa-users mr-1"></i>{{ $booking->attendees_count }} peserta</span>
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-2">
                        @can('cancel', $booking)
                            <form method="POST" action="{{ route('bookings.cancel', $booking) }}" onsubmit="return confirm('Batalkan booking ini?')">@csrf<button class="rounded-xl border border-rose-200 px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-300 dark:hover:bg-rose-950/40">Batalkan</button></form>
                        @endcan
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 py-16 text-center dark:border-slate-700">
                <i class="fas fa-calendar-xmark text-4xl text-slate-300"></i>
                <p class="mt-3 text-sm font-semibold text-slate-500">Belum ada booking.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $bookings->links() }}</div>
@endsection
