@extends('layouts.app')

@section('title', 'Kelola Display - MeetSpace')

@section('content')
    <x-admin-nav />

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Perangkat Display</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Daftarkan satu monitor pintu untuk setiap ruangan dan kelola kredensial aksesnya.</p>
    </div>

    <form method="POST" action="{{ route('admin.displays.store') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:grid-cols-12">
        @csrf
        <div class="sm:col-span-5"><label class="text-xs font-bold text-slate-600 dark:text-slate-300">Ruangan</label><select name="room_id" required class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900"><option value="">Pilih ruangan tanpa display</option>@foreach($rooms as $room)<option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>{{ $room->code }} — {{ $room->name }}</option>@endforeach</select></div>
        <div class="sm:col-span-5"><label class="text-xs font-bold text-slate-600 dark:text-slate-300">Nama Perangkat</label><input name="display_name" value="{{ old('display_name') }}" required placeholder="Contoh: Monitor Pintu Kalibaru" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900"></div>
        <button class="self-end rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700 sm:col-span-2"><i class="fas fa-plus mr-1"></i>Daftar</button>
    </form>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        @forelse($displays as $display)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-start justify-between gap-3"><div><h2 class="font-bold text-slate-900 dark:text-white"><i class="fas fa-display mr-2 text-blue-500"></i>{{ $display->display_name }}</h2><p class="mt-1 text-xs text-slate-500">{{ $display->room->code }} · {{ $display->room->name }}</p></div><span @class(['rounded-full px-2.5 py-1 text-[10px] font-bold', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' => $display->last_active?->greaterThan(now()->subMinutes(5)), 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300' => ! $display->last_active?->greaterThan(now()->subMinutes(5))])>{{ $display->last_active?->greaterThan(now()->subMinutes(5)) ? 'ONLINE' : 'OFFLINE' }}</span></div>
                <div class="mt-4 rounded-xl bg-slate-50 p-3 dark:bg-slate-900/60"><div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Token rahasia display</div><code class="mt-1 block break-all text-xs text-slate-700 dark:text-slate-300">{{ $display->display_token }}</code></div>
                <div class="mt-4 flex flex-wrap justify-end gap-2">
                    <form method="POST" action="{{ route('admin.displays.regenerate', $display) }}" onsubmit="return confirm('Ganti token? Perangkat dengan URL lama akan terputus.')">@csrf<button class="rounded-lg border border-amber-200 px-3 py-2 text-xs font-bold text-amber-700 hover:bg-amber-50 dark:border-amber-900 dark:text-amber-300">Ganti Token</button></form>
                    <form method="POST" action="{{ route('admin.displays.destroy', $display) }}" onsubmit="return confirm('Hapus perangkat display ini?')">@csrf @method('DELETE')<button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-300">Hapus</button></form>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800 lg:col-span-2">Belum ada perangkat display terdaftar.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $displays->links() }}</div>
@endsection
