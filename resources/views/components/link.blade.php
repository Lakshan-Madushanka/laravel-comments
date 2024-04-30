@props(['route' => '#', 'type' => 'navigate'])

@if ($type === 'navigate')
    <a
        wire:navigate
        href="{{ $route }}"
        {{ $attributes->merge(['class' => 'font-medium text-blue-600 dark:text-blue-500 hover:underline']) }}
    >
        {{ $slot }}
    </a>
@else
    <span
        {{ $attributes->merge(['class' => 'inline-block cursor-pointer font-medium text-blue-600 dark:text-blue-500 hover:border-b hover:border-blue-600']) }}
    >
        {{ $slot }}
    </span>
@endif
