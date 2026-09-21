<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MeetSpace')</title>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased transition-colors dark:bg-slate-900 dark:text-slate-100">
    <header class="border-b border-sky-100 bg-sky-50/80 dark:border-slate-700 dark:bg-slate-800">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 shrink-0">
                <img src="{{ asset('logo.png') }}" alt="MeetSpace" class="h-9 w-auto">
            </a>
            <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi utama">
                <a href="{{ route('dashboard') }}" @class(['rounded-lg px-3 py-2 text-xs font-bold transition', 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' => request()->routeIs('dashboard'), 'text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700' => !request()->routeIs('dashboard')])>Dashboard</a>
                <a href="{{ route('bookings.index') }}" @class(['rounded-lg px-3 py-2 text-xs font-bold transition', 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' => request()->routeIs('bookings.*'), 'text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700' => !request()->routeIs('bookings.*')])>Booking Saya</a>
                <a href="{{ route('calendar.index') }}" @class(['rounded-lg px-3 py-2 text-xs font-bold transition', 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' => request()->routeIs('calendar.*'), 'text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700' => !request()->routeIs('calendar.*')])>Kalender</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.bookings.index') }}" @class(['rounded-lg px-3 py-2 text-xs font-bold transition', 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' => request()->routeIs('admin.*'), 'text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700' => !request()->routeIs('admin.*')])>Persetujuan</a>
                @endif
            </nav>
            <div class="flex items-center gap-3">
                <button type="button" data-theme-toggle class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 dark:border-slate-600 dark:text-slate-300">
                    <i class="fas fa-circle-half-stroke mr-1"></i> Tema
                </button>
                <div class="hidden text-right sm:block">
                    <div class="text-sm font-bold">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] uppercase text-slate-400">{{ auth()->user()->role }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-xl bg-slate-800 px-3 py-2 text-xs font-bold text-white hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300">
                <i class="fas fa-circle-check"></i>{{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300">
                <i class="fas fa-circle-exclamation mt-0.5"></i>{{ $errors->first() }}
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
