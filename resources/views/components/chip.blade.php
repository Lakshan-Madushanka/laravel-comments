
@php use LakM\Comments\Helpers; @endphp

@props([
    'active' => false
])

@php
    $bgColor = "";

    if (Helpers::isDefaultTheme()) {
        $bgColor = "bg-gray-200 dark:bg-slate-700";
    }

    if (Helpers::isDefaultTheme() && $active) {
        $bgColor = "bg-gray-400/75 dark:bg-slate-500";
    }

    if (Helpers::isGithubTheme()) {
        $bgColor = "bg-gray-200 dark:bg-slate-800";
    }

    if (Helpers::isGithubTheme() && $active) {
        $bgColor = "bg-gray-400/75 dark:bg-slate-700";
    }

    if (Helpers::isModernTheme()) {
        $bgColor = "bg-gray-100 dark:bg-slate-800";
    }

    if (Helpers::isModernTheme() && $active) {
        $bgColor = "bg-gray-200 dark:bg-slate-700";
    }
@endphp

<div {{ $attributes
    ->class([
        $bgColor,
        'px-2 py-1 rounded cursor-pointer transition ml-[-6px] sm:ml-[2px] text-nowrap',
        "border hover:bg-gray-300 dark:hover:bg-slate-800 dark:border-0" => Helpers::isDefaultTheme(),
        "hover:bg-gray-300 dark:hover:bg-slate-900" => Helpers::isGithubTheme(),
        "rounded-lg hover:bg-gray-200" => Helpers::isModernTheme(),
    ])
    ->merge() }}
>
    {{ $slot }}
</div>
