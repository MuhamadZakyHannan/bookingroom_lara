<x-auth-layout title="Daftar Akun" subtitle="Buat akun baru Anda">
    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf
        <x-form-input label="Nama Lengkap" name="name" icon="fa-user" placeholder="Contoh: Budi Santoso" autocomplete="name" autofocus />
        <x-form-input label="Alamat Email Perusahaan" name="email" type="email" icon="fa-envelope" placeholder="nama@company.com" autocomplete="email" />
        <x-form-input label="Password" name="password" type="password" icon="fa-lock" placeholder="Minimal 8 karakter" autocomplete="new-password" />
        <x-form-input label="Ulangi Password" name="password_confirmation" type="password" icon="fa-shield-alt" placeholder="Konfirmasi password" autocomplete="new-password" />

        <p class="text-xs leading-5 text-slate-500 dark:text-slate-400">Gunakan huruf besar, huruf kecil, angka, dan karakter khusus.</p>

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700">
            <i class="fas fa-check-circle"></i> Daftar Akun Sekarang
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
        Sudah memiliki akun?
        <a href="{{ route('login') }}" class="ml-1 font-bold text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-400">Masuk Di Sini</a>
    </p>
</x-auth-layout>
