@php use LakM\Comments\Helpers; @endphp
@props(['route' => '#', 'type' => 'navigate'])

@php
    $class= "";

    if (!Helpers::isModernTheme()) {
        $class = "hover:border-b";
    }
@endphp

@if ($type === 'navigate')
    <a
        wire:navigate
        href="{{ $route }}"
        {{ $attributes->merge(['class' => "$class font-medium transition"]) }}
    >
        {{ $slot }}
    </a>
@elseif ($type === 'a')
    <a
        href="{{ $route }}"
        {{ $attributes->merge(['class' => "$class font-medium transition"]) }}
    >
        {{ $slot }}
    </a>
@else
    <span
        {{ $attributes->merge(['class' => " $class inline-block cursor-pointer font-medium transition"]) }}
    >
        {{ $slot }}
    </span>
@endif
