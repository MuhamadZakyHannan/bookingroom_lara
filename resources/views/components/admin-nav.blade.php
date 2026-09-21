<nav class="mb-6 flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm dark:border-slate-700 dark:bg-slate-800" aria-label="Menu administrasi">
    @php
        $items = [
            ['route' => 'admin.bookings.index', 'pattern' => 'admin.bookings.*', 'icon' => 'fa-clipboard-check', 'label' => 'Persetujuan'],
            ['route' => 'admin.conflicts.index', 'pattern' => 'admin.conflicts.*', 'icon' => 'fa-scale-balanced', 'label' => 'Konflik SAW'],
            ['route' => 'admin.rooms.index', 'pattern' => 'admin.rooms.*', 'icon' => 'fa-door-open', 'label' => 'Ruangan'],
            ['route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'icon' => 'fa-users', 'label' => 'Pengguna'],
            ['route' => 'admin.displays.index', 'pattern' => 'admin.displays.*', 'icon' => 'fa-display', 'label' => 'Display'],
        ];
    @endphp
    @foreach($items as $item)
        <a href="{{ route($item['route']) }}" @class([
            'shrink-0 rounded-xl px-4 py-2.5 text-xs font-bold transition',
            'bg-blue-600 text-white shadow-sm' => request()->routeIs($item['pattern']),
            'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700' => ! request()->routeIs($item['pattern']),
        ])><i class="fas {{ $item['icon'] }} mr-2"></i>{{ $item['label'] }}</a>
    @endforeach
</nav>
