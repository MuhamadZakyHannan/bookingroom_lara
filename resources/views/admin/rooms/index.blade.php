@extends('layouts.app')

@section('title', 'Kelola Ruangan - MeetSpace')

@section('content')
    <x-admin-nav />

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Kelola Ruangan</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Data master ruangan, fasilitas, kapasitas, foto, dan status operasional.</p>
    </div>

    <details class="mb-6 rounded-2xl border border-blue-200 bg-blue-50/60 p-5 dark:border-blue-900 dark:bg-blue-950/20" @if($errors->hasAny(['code','name','capacity','location','image'])) open @endif>
        <summary class="cursor-pointer font-bold text-blue-700 dark:text-blue-300"><i class="fas fa-plus-circle mr-2"></i>Tambah Ruangan</summary>
        <form method="POST" action="{{ route('admin.rooms.store') }}" enctype="multipart/form-data" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            @csrf
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Kode<input name="code" value="{{ old('code') }}" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm uppercase dark:border-slate-700 dark:bg-slate-900"></label>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300 lg:col-span-2">Nama Ruangan<input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900"></label>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Kapasitas<input type="number" name="capacity" value="{{ old('capacity', 1) }}" min="1" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900"></label>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300 lg:col-span-2">Lokasi<input name="location" value="{{ old('location') }}" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900"></label>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Lantai<input name="floor" value="{{ old('floor') }}" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900"></label>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Status<select name="status" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">@foreach($statuses as $status)<option value="{{ $status->value }}">{{ $status->label() }}</option>@endforeach</select></label>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300 lg:col-span-2">Fasilitas<input name="facilities" value="{{ old('facilities') }}" placeholder="AC, Proyektor, Whiteboard" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900"></label>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300 lg:col-span-2">Foto (maks. 5 MB)<input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"></label>
            <label class="text-xs font-bold text-slate-600 dark:text-slate-300 lg:col-span-4">Deskripsi<textarea name="description" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900">{{ old('description') }}</textarea></label>
            <button class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700 lg:col-start-4"><i class="fas fa-save mr-2"></i>Simpan Ruangan</button>
        </form>
    </details>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        @foreach($rooms as $room)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="flex gap-4 p-5">
                    <img src="{{ $room->image_url }}" alt="{{ $room->name }}" class="h-24 w-32 rounded-xl object-cover">
                    <div class="min-w-0 flex-1"><div class="flex items-start justify-between gap-2"><div><h2 class="font-bold text-slate-900 dark:text-white">{{ $room->name }}</h2><p class="mt-1 text-xs text-slate-500">{{ $room->code }} · {{ $room->location }}</p></div><span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ $room->status }}</span></div><p class="mt-3 text-xs text-slate-500">{{ $room->capacity }} orang · {{ $room->bookings_count }} booking · {{ $room->displays_count }} display</p></div>
                </div>
                <details class="border-t border-slate-100 dark:border-slate-700">
                    <summary class="cursor-pointer px-5 py-3 text-xs font-bold text-blue-600 dark:text-blue-400">Edit data ruangan</summary>
                    <form method="POST" action="{{ route('admin.rooms.update', $room) }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-3 bg-slate-50 p-5 dark:bg-slate-900/40 sm:grid-cols-2">
                        @csrf @method('PUT')
                        <input name="code" value="{{ $room->code }}" required class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm uppercase dark:border-slate-700 dark:bg-slate-900">
                        <input name="name" value="{{ $room->name }}" required class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <input type="number" name="capacity" value="{{ $room->capacity }}" min="1" required class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">@foreach($statuses as $status)<option value="{{ $status->value }}" @selected($room->status === $status->value)>{{ $status->label() }}</option>@endforeach</select>
                        <input name="location" value="{{ $room->location }}" required class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <input name="floor" value="{{ $room->floor }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                        <input name="facilities" value="{{ $room->facilities }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 sm:col-span-2">
                        <textarea name="description" rows="2" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 sm:col-span-2">{{ $room->description }}</textarea>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 sm:col-span-2">
                        <button class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white sm:col-start-2"><i class="fas fa-save mr-1"></i>Simpan Perubahan</button>
                    </form>
                    <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}" class="bg-slate-50 px-5 pb-5 text-right dark:bg-slate-900/40" onsubmit="return confirm('Hapus ruangan ini?')">
                        @csrf @method('DELETE')
                        <button @disabled($room->bookings_count > 0 || $room->displays_count > 0) class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-600 disabled:cursor-not-allowed disabled:opacity-40 dark:border-rose-900 dark:text-rose-300">Hapus Ruangan</button>
                    </form>
                </details>
            </article>
        @endforeach
    </div>
    <div class="mt-6">{{ $rooms->links() }}</div>
@endsection
