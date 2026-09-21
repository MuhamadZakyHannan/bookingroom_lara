@extends('layouts.app')

@section('title', 'Dashboard Ruangan - MeetSpace')

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Dashboard Ruangan</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pantau ketersediaan dan jadwal ruang rapat perusahaan.</p>
        </div>
        <span class="w-fit rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-bold text-blue-700 dark:border-blue-800 dark:bg-blue-950/50 dark:text-blue-300">
            Migrasi Laravel tahap awal
        </span>
    </div>

    <section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Ringkasan dashboard">
        @foreach ([
            ['label' => 'Total Ruangan', 'value' => $stats['rooms'], 'icon' => 'fa-door-open', 'color' => 'blue'],
            ['label' => 'Tersedia Siap Pakai', 'value' => $stats['available'], 'icon' => 'fa-circle-check', 'color' => 'emerald'],
            ['label' => 'Jadwal Hari Ini', 'value' => $stats['today'], 'icon' => 'fa-clock', 'color' => 'indigo'],
            ['label' => 'Total Pengguna', 'value' => $stats['users'], 'icon' => 'fa-users', 'color' => 'amber'],
        ] as $stat)
            <article class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-5 shadow-sm dark:border-slate-700/80 dark:bg-slate-800">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $stat['label'] }}</div>
                    <div class="mt-1 text-2xl font-extrabold text-slate-800 dark:text-white">{{ $stat['value'] }}</div>
                </div>
                <div @class([
                    'flex h-12 w-12 items-center justify-center rounded-xl text-xl',
                    'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' => $stat['color'] === 'blue',
                    'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' => $stat['color'] === 'emerald',
                    'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' => $stat['color'] === 'indigo',
                    'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400' => $stat['color'] === 'amber',
                ])>
                    <i class="fas {{ $stat['icon'] }}"></i>
                </div>
            </article>
        @endforeach
    </section>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <section class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm dark:border-slate-700/80 dark:bg-slate-800">
                <div class="mb-6 flex items-center justify-between gap-3">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white">
                        <i class="fas fa-list-ul text-blue-600 dark:text-blue-400"></i> Daftar Ruangan Rapat
                    </h2>
                    <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ $rooms->count() }} ditemukan</span>
                </div>

                <form method="GET" class="mb-6 grid grid-cols-1 gap-3 rounded-xl border border-slate-200/80 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/50 sm:grid-cols-12">
                    <input name="search" value="{{ $filters['search'] }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-800 sm:col-span-5" placeholder="Cari nama, kode, lokasi, atau fasilitas">
                    <select name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-800 sm:col-span-3">
                        <option value="">Semua Status</option>
                        @foreach (['available' => 'Tersedia', 'occupied' => 'Terpakai', 'maintenance' => 'Perawatan'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="min_capacity" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-800 sm:col-span-2">
                        <option value="0">Kapasitas</option>
                        @foreach ([5, 10, 20] as $capacity)
                            <option value="{{ $capacity }}" @selected($filters['minimumCapacity'] === $capacity)>≥ {{ $capacity }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white hover:bg-blue-700 sm:col-span-2"><i class="fas fa-search mr-1"></i>Cari</button>
                </form>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @forelse ($rooms as $room)
                        <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-blue-300 dark:border-slate-700 dark:bg-slate-800/90">
                            <div class="relative h-44 overflow-hidden bg-slate-100 dark:bg-slate-900">
                                <img src="{{ $room->image_url }}" alt="{{ $room->name }}" class="h-full w-full object-cover">
                                <span @class([
                                    'absolute right-3 top-3 rounded-lg px-2.5 py-1 text-xs font-bold text-white shadow-md',
                                    'bg-emerald-500' => $room->status === 'available',
                                    'bg-amber-500' => $room->status === 'occupied',
                                    'bg-slate-600' => $room->status === 'maintenance',
                                ])>{{ ['available' => 'Tersedia', 'occupied' => 'Terpakai', 'maintenance' => 'Perawatan'][$room->status] ?? $room->status }}</span>
                            </div>
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-bold text-slate-900 dark:text-white">{{ $room->name }}</h3>
                                    <span class="rounded border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-[11px] dark:border-slate-600 dark:bg-slate-700">{{ $room->code }}</span>
                                </div>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400"><i class="fas fa-location-dot mr-1 text-rose-500"></i>{{ $room->location }} · {{ $room->floor }}</p>
                                <p class="mt-2 text-xs font-medium text-slate-600 dark:text-slate-300"><i class="fas fa-users mr-1 text-blue-500"></i>Kapasitas {{ $room->capacity }} orang</p>
                                <p class="mt-3 line-clamp-2 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $room->description }}</p>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-2 py-12 text-center text-sm text-slate-400">Tidak ada ruangan yang sesuai dengan filter.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm dark:border-slate-700/80 dark:bg-slate-800">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-700">
                    <h2 class="font-bold text-slate-900 dark:text-white"><i class="fas fa-calendar-day mr-2 text-blue-600"></i>Jadwal Hari Ini</h2>
                    <span class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ now()->translatedFormat('d M Y') }}</span>
                </div>
                <div class="space-y-3">
                    @forelse ($todayBookings as $booking)
                        <article class="rounded-xl border border-slate-200/80 bg-slate-50 p-3.5 dark:border-slate-700 dark:bg-slate-900/60">
                            <div class="text-xs font-bold text-blue-700 dark:text-blue-300">{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</div>
                            <h3 class="mt-1 text-sm font-bold">{{ $booking->title }}</h3>
                            <p class="mt-1 text-xs text-slate-500">{{ $booking->room->name }}</p>
                        </article>
                    @empty
                        <p class="py-8 text-center text-xs text-slate-500">Tidak ada agenda rapat hari ini.</p>
                    @endforelse
                </div>
            </section>

            @if (auth()->user()->isAdmin())
                <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm dark:border-slate-700/80 dark:bg-slate-800">
                    <h2 class="mb-4 border-b border-slate-100 pb-3 font-bold dark:border-slate-700"><i class="fas fa-clipboard-check mr-2 text-amber-500"></i>Persetujuan Peminjaman</h2>
                    <div class="space-y-3">
                        @forelse ($pendingBookings as $booking)
                            <article class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900/50">
                                <h3 class="truncate text-sm font-bold">{{ $booking->title }}</h3>
                                <p class="mt-2 text-xs text-slate-500">{{ $booking->room->name }}</p>
                                <p class="mt-2 text-xs text-slate-500">{{ $booking->date->translatedFormat('d M Y') }} · {{ substr($booking->start_time, 0, 5) }}</p>
                            </article>
                        @empty
                            <p class="py-5 text-center text-xs text-slate-500">Tidak ada pengajuan tertunda.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm dark:border-slate-700/80 dark:bg-slate-800">
                    <h2 class="mb-4 border-b border-slate-100 pb-3 font-bold dark:border-slate-700"><i class="fas fa-display mr-2 text-blue-600"></i>Monitoring Display</h2>
                    <div class="space-y-2">
                        @forelse ($displays as $display)
                            @php($online = $display->last_active?->greaterThanOrEqualTo(now()->subMinutes(2)))
                            <div class="flex items-center justify-between rounded-lg px-2 py-2 text-xs">
                                <span class="truncate font-semibold">{{ $display->room->name }}</span>
                                <span class="ml-3 flex shrink-0 items-center gap-1.5 font-bold {{ $online ? 'text-emerald-600' : 'text-rose-600' }}">
                                    <span class="h-2 w-2 rounded-full {{ $online ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>{{ $online ? 'Online' : 'Offline' }}
                                </span>
                            </div>
                        @empty
                            <p class="py-5 text-center text-xs text-slate-500">Belum ada display terdaftar.</p>
                        @endforelse
                    </div>
                </section>
            @endif
        </aside>
    </div>
@endsection
