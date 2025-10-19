@props(["loadingTarget" => "", "dirtyTarget" => "", "type" => "submit", "size" => "md", "severity" => "primary"])

@php
    $class = $size === "sm" ? "py-1! px-2! text-sm! " : "py-1 px-2 text-sm lg:text-base lg:py-[0.4rem] lg:px-3 ";

    $severityClass = match ($severity) {
        "info" => "hover:bg-[" . config('commenter.button_hover_color') . "]! ",
        "primary" => "hover:bg-[" . config('commenter.button_hover_color') . "]! ",
    };

     $severityStyle = match ($severity) {
        "info" => 'background: ' . config('commenter.button_color'),
        "primary" => 'background: ' . config('commenter.button_color'),
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
        {{ $attributes->merge(["class" => $class . $severityClass . "inline-flex items-center gap-x-2 font-semibold justify-center rounded-sm border border-transparent text-white disabled:opacity-50 dark:focus:outline-hidden dark:focus:ring-1 dark:focus:ring-gray-600 cursor-not-allowed"]) }}
        @style([$severityStyle])
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
        {{ $attributes->merge(["class" => $class . $severityClass . "inline-flex justify-center items-center gap-x-2 font-semibold rounded-sm border border-transparent text-white disabled:opacity-50 dark:focus:outline-hidden dark:focus:ring-1 dark:focus:ring-gray-600"]) }}
        @style([$severityStyle])
    >
        <span>{{ $slot }}</span>
        <x-commenter::spin color="white"/>
    </button>
@endif

@if ($type === "button")
    <button
        wire:loading.remove
        @if ($loadingTarget)
            wire:target="{{ $loadingTarget }}"
        @endif
        type="button"
        {{ $attributes->merge(["class" => $class . $severityClass . "inline-flex hover:cursor-pointer justify-center items-center gap-x-2 font-semibold rounded-sm border border-transparent text-white disabled:opacity-50 dark:focus:outline-hidden dark:focus:ring-1 dark:focus:ring-gray-600"]) }}
        @style([$severityStyle])
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
            {{ $attributes->merge(["class" => $class . $severityClass . "inline-flex justify-center items-center gap-x-2 font-semibold rounded-sm border border-transparent text-white disabled:opacity-50 dark:focus:outline-hidden dark:focus:ring-1 dark:focus:ring-gray-600 cursor-not-allowed"]) }}
            @style([$severityStyle])
        >
            <span>{{ $slot }}</span>
            <x-commenter::spin color="white"/>
        </button>
    </div>
@endif
