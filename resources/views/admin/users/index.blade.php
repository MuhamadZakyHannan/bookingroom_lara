@extends('layouts.app')

@section('title', 'Kelola Pengguna - MeetSpace')

@section('content')
    <x-admin-nav />

    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Kelola Pengguna</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Admin mengatur hak akses. Pembuatan akun baru tetap melalui halaman pendaftaran.</p>
        </div>
        <form method="GET" class="flex w-full max-w-md gap-2">
            <input name="search" value="{{ $search }}" placeholder="Cari nama atau email" class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
            <button class="rounded-xl bg-blue-600 px-4 text-sm font-bold text-white"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left dark:divide-slate-700">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 dark:bg-slate-900/60 dark:text-slate-400"><tr><th class="px-5 py-4">Pengguna</th><th class="px-5 py-4">Aktivitas</th><th class="px-5 py-4">Role</th><th class="px-5 py-4 text-right">Tindakan</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/80">
                    @forelse($users as $user)
                        <tr>
                            <td class="px-5 py-4"><div class="font-bold text-slate-900 dark:text-white">{{ $user->name }} @if(auth()->id() === $user->id)<span class="ml-1 text-[10px] text-blue-500">ANDA</span>@endif</div><div class="mt-1 text-xs text-slate-500">{{ $user->email }}</div></td>
                            <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $user->bookings_count }} booking</td>
                            <td class="px-5 py-4">
                                <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex min-w-52 gap-2">
                                    @csrf @method('PATCH')
                                    <select name="role" @disabled(auth()->id() === $user->id) class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs dark:border-slate-700 dark:bg-slate-900">@foreach($roles as $role)<option value="{{ $role->value }}" @selected($user->role === $role->value)>{{ $role->label() }}</option>@endforeach</select>
                                    <button @disabled(auth()->id() === $user->id) class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white disabled:opacity-40">Simpan</button>
                                </form>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')">
                                    @csrf @method('DELETE')
                                    <button @disabled(auth()->id() === $user->id || $user->bookings_count > 0) class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-600 disabled:cursor-not-allowed disabled:opacity-40 dark:border-rose-900 dark:text-rose-300">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-14 text-center text-sm text-slate-500">Pengguna tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p class="mt-3 text-xs text-slate-400">Akun sendiri dan pengguna dengan riwayat booking tidak dapat dihapus. Minimal satu administrator selalu dipertahankan.</p>
    <div class="mt-6">{{ $users->links() }}</div>
@endsection
