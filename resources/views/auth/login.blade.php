<x-auth-layout title="Masuk" subtitle="Masuk ke akun Anda">
    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf
        <x-form-input label="Alamat Email" name="email" type="email" icon="fa-envelope" placeholder="nama@company.com" autocomplete="email" autofocus />
        <x-form-input label="Password" name="password" type="password" icon="fa-lock" placeholder="••••••••" autocomplete="current-password" />

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700">
            <i class="fas fa-sign-in-alt"></i> Masuk Sekarang
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
        Belum punya akun?
        <a href="{{ route('register') }}" class="ml-1 font-bold text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-400">Daftar Akun Baru</a>
    </p>
</x-auth-layout>
