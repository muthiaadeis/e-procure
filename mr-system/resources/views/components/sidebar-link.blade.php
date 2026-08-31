@props(['href', 'active' => false, 'disabled' => false])

@php
$classes = $active
    ? 'bg-indigo-50 text-indigo-700 font-semibold'
    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';

$disabledClasses = 'text-gray-300 cursor-not-allowed';
@endphp

@if($disabled)
    <div {{ $attributes->merge(['class' => "flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {$disabledClasses}"]) }}>
        {{ $slot }}
        <span class="ml-auto text-[10px] font-medium bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded">Segera</span>
    </div>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {$classes}"]) }}>
        {{ $slot }}
    </a>
@endif
