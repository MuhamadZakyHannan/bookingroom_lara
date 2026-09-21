@props(['status'])

@php
    $classes = match ($status->value) {
        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
        'confirmed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
        'completed' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300',
        default => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    };
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase {$classes}") }}>{{ $status->label() }}</span>
