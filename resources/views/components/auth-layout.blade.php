@props(['title', 'subtitle'])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - MeetSpace</title>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-100 p-4 text-slate-800 antialiased transition-colors dark:bg-slate-900 dark:text-slate-100 flex items-center justify-center">
    <main class="w-full max-w-md">
        <section class="relative rounded-2xl border border-slate-100 bg-white p-8 shadow-xl dark:border-slate-700/80 dark:bg-slate-800">
            <button type="button" data-theme-toggle class="absolute right-4 top-4 rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200" aria-label="Ganti tema">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:inline"></i>
            </button>

            <header class="mb-6 text-center">
                <div class="mb-3 flex justify-center">
                    <img src="{{ asset('logo.png') }}" alt="Logo MeetSpace" class="h-12 w-auto object-contain">
                </div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Sistem Pemesanan Ruangan</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
            </header>

            @if (session('success'))
                <div class="mb-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300">
                    <i class="fas fa-exclamation-circle mt-0.5 text-rose-500"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            {{ $slot }}
        </section>
    </main>
</body>
</html>
