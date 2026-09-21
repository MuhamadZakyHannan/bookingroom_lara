@props([
    'label',
    'name',
    'type' => 'text',
    'icon' => 'fa-circle',
    'placeholder' => '',
    'autocomplete' => null,
])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $label }}</label>
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
            <i class="fas {{ $icon }}"></i>
        </span>
        <input
            id="{{ $name }}"
            type="{{ $type }}"
            name="{{ $name }}"
            value="{{ $type === 'password' ? '' : old($name) }}"
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            placeholder="{{ $placeholder }}"
            required
            {{ $attributes->class('w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:bg-slate-900') }}
        >
    </div>
</div>
