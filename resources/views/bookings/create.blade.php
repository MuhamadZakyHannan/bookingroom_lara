@extends('layouts.app')

@section('title', 'Pesan Ruangan - MeetSpace')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="text-xs font-bold text-blue-600 hover:underline dark:text-blue-400"><i class="fas fa-arrow-left mr-1"></i>Kembali ke dashboard</a>
            <h1 class="mt-3 text-2xl font-bold text-slate-900 dark:text-white">Pesan Ruang Rapat</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Isi agenda dan waktu pemakaian dengan lengkap.</p>
        </div>

        <form method="POST" action="{{ route('bookings.store') }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-8">
            @csrf

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="room_id" class="mb-1.5 block text-sm font-semibold">Ruangan <span class="text-rose-500">*</span></label>
                    <select id="room_id" name="room_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <option value="">Pilih ruangan</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" @selected((int) old('room_id', $selectedRoomId) === $room->id)>{{ $room->code }} — {{ $room->name }} ({{ $room->capacity }} orang)</option>
                        @endforeach
                    </select>
                    @error('room_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="user_name" class="mb-1.5 block text-sm font-semibold">Nama Pemesan <span class="text-rose-500">*</span></label>
                    <input id="user_name" name="user_name" value="{{ old('user_name', auth()->user()->name) }}" required maxlength="100" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
                </div>
                <div>
                    <label for="user_dept" class="mb-1.5 block text-sm font-semibold">Divisi <span class="text-rose-500">*</span></label>
                    <input id="user_dept" name="user_dept" value="{{ old('user_dept') }}" required maxlength="100" placeholder="Contoh: Komersial" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
                </div>

                <div class="sm:col-span-2">
                    <label for="title" class="mb-1.5 block text-sm font-semibold">Agenda Pertemuan <span class="text-rose-500">*</span></label>
                    <input id="title" name="title" value="{{ old('title') }}" required maxlength="150" placeholder="Contoh: Evaluasi keamanan server" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
                </div>

                <div>
                    <label for="date" class="mb-1.5 block text-sm font-semibold">Tanggal <span class="text-rose-500">*</span></label>
                    <input id="date" type="date" name="date" min="{{ today()->format('Y-m-d') }}" value="{{ old('date', $selectedDate) }}" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="start_time" class="mb-1.5 block text-sm font-semibold">Mulai <span class="text-rose-500">*</span></label>
                        <input id="start_time" type="time" name="start_time" value="{{ old('start_time', '09:00') }}" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
                    </div>
                    <div>
                        <label for="end_time" class="mb-1.5 block text-sm font-semibold">Selesai <span class="text-rose-500">*</span></label>
                        <input id="end_time" type="time" name="end_time" value="{{ old('end_time', '10:00') }}" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
                    </div>
                </div>

                <div>
                    <label for="activity_type" class="mb-1.5 block text-sm font-semibold">Jenis Kegiatan <span class="text-rose-500">*</span></label>
                    <select id="activity_type" name="activity_type" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
                        @foreach($activityTypes as $type)
                            <option value="{{ $type->value }}" @selected(old('activity_type', 'internal_divisi') === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="attendees_count" class="mb-1.5 block text-sm font-semibold">Jumlah Peserta <span class="text-rose-500">*</span></label>
                    <input id="attendees_count" type="number" name="attendees_count" min="1" value="{{ old('attendees_count', 1) }}" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">
                </div>

                <div class="sm:col-span-2">
                    <label for="purpose" class="mb-1.5 block text-sm font-semibold">Tujuan / Catatan</label>
                    <textarea id="purpose" name="purpose" rows="4" maxlength="2000" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900" placeholder="Tambahkan kebutuhan atau catatan rapat">{{ old('purpose') }}</textarea>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 dark:border-slate-700 sm:flex-row sm:justify-end">
                <a href="{{ route('dashboard') }}" class="rounded-xl bg-slate-100 px-5 py-3 text-center text-sm font-bold text-slate-700 dark:bg-slate-700 dark:text-slate-200">Batal</a>
                <button type="submit" class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700"><i class="fas fa-check-circle mr-2"></i>Kirim Pengajuan</button>
            </div>
        </form>
    </div>
@endsection
