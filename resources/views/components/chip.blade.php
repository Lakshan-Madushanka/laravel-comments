
@php use LakM\Commenter\Helpers; @endphp

@props([
    'active' => false
])

@php
    $bgColor = "";

    if (Helpers::isDefaultTheme()) {
        $bgColor = "bg-[" . config('commenter.bg_primary_color') . "]" . " no-dark:!bg-slate-700";
    }

    if (Helpers::isDefaultTheme() && $active) {
        $bgColor = "bg-[" . config('commenter.active_color') . "]" . " no-dark:!bg-slate-500";
    }

    if (Helpers::isGithubTheme()) {
        $bgColor = "bg-[" . config('commenter.bg_primary_color') . "]" . " no-dark:!bg-slate-800";
    }

    if (Helpers::isGithubTheme() && $active) {
        $bgColor = "bg-[" . config('commenter.active_color') . "]" . " no-dark:!bg-slate-700";
    }

    if (Helpers::isModernTheme()) {
        $bgColor = "bg-[" . config('commenter.bg_primary_color') . "]" . " no-dark:!bg-slate-800";
    }

    if (Helpers::isModernTheme() && $active) {
        $bgColor = "bg-[" . config('commenter.active_color') . "]" . " no-dark:!bg-slate-700";
    }
@endphp

<div {{ $attributes
    ->class([
        $bgColor,
        'px-2 border shadow py-1 no-dark:!text-black rounded cursor-pointer transition ms-[-6px] sm:ms-[2px] text-nowrap no-dark:!text-white text-sm',
        "hover:!bg-["  . config('commenter.hover_color') . "]",
        "border no-dark:hover:!bg-slate-800 no-dark:border-0" => Helpers::isDefaultTheme(),
        "dark:hover:!bg-slate-900" => Helpers::isGithubTheme(),
        "rounded-lg no-dark:hover:!bg-slate-600" => Helpers::isModernTheme(),
    ])
    ->merge() }}
    @style([
        'color: ' . config('commenter.primary_color'),
        'background: ' . config('commenter.bg_primary_color') => !$active && (Helpers::isGithubTheme() || Helpers::isModernTheme()),
        'background: ' . config('commenter.active_color') => $active && (Helpers::isGithubTheme() || Helpers::isModernTheme()),
    ])
>
    {{ $slot }}
</div>
