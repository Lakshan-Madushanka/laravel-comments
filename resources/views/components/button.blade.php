@props(["loadingTarget" => "", "dirtyTarget" => "", "type" => "submit", "size" => "md", "severity" => "primary"])

@php
    $class = $size === "sm" ? "!py-1 !px-2 !text-sm " : "py-1 px-2 text-sm lg:text-base lg:py-[0.4rem] lg:px-3 ";

    $severity = match ($severity) {
        "primary" => "bg-blue-600 hover:bg-blue-700 ",
        "info" => "bg-gray-600 hover:bg-gray-700 ",
    };
@endphp

@if ($type === "submit")
    <button
        wire:loading.remove
        wire:dirty.remove.attr="disabled"
        wire:dirty.class="cursor-pointer"
        @if ($dirtyTarget || $loadingTarget)
            wire:target="{{ $dirtyTarget }},{{ $loadingTarget }}"
        @endif
        disabled
        type="submit"
        {{ $attributes->merge(["class" => $class . $severity . "inline-flex items-center gap-x-2 font-semibold justify-center rounded border border-transparent text-white disabled:opacity-50 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600 cursor-not-allowed"]) }}
    >
        {{ $slot }}
    </button>

    <button
        wire:loading.inline-flex
        @if ($loadingTarget)
            wire:target="{{ $loadingTarget }}"
        @endif
        type="button"
        disabled
        {{ $attributes->merge(["class" => $class . $severity . "inline-flex justify-center items-center gap-x-2 font-semibold rounded border border-transparent text-white disabled:opacity-50 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600"]) }}
    >
        <span>{{ $slot }}</span>
        <x-comments::spin />
    </button>
@endif

@if ($type === "button")
    <button
        wire:loading.remove
        @if ($loadingTarget)
            wire:target="{{ $loadingTarget }}"
        @endif
        type="button"
        {{ $attributes->merge(["class" => $class . $severity . "inline-flex justify-center items-center gap-x-2 font-semibold rounded border border-transparent text-white disabled:opacity-50 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600"]) }}
    >
        {{ $slot }}
    </button>

    <div
        wire:loading.delay.longer
        @if ($loadingTarget)
            wire:target="{{ $loadingTarget }}"
        @endif
    >
        <button
            wire:loading.inline-flex
            @if ($loadingTarget)
                wire:target="{{ $loadingTarget }}"
            @endif
            disabled
            type="button"
            {{ $attributes->merge(["class" => $class . $severity . "inline-flex justify-center items-center gap-x-2 font-semibold rounded border border-transparent text-white disabled:opacity-50 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600 cursor-not-allowed"]) }}
        >
            <span>{{ $slot }}</span>
            <x-comments::spin />
        </button>
    </div>
@endif
