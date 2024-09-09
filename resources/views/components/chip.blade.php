@php use LakM\Comments\Helpers; @endphp
<div {{ $attributes
    ->class([
        'px-2 py-1 rounded cursor-pointer transition ml-[-6px] sm:ml-[2px] text-nowrap',
        'border hover:bg-gray-300 dark:bg-slate-700 dark:hover:bg-slate-800 dark:border-0' => Helpers::isDefaultTheme(),
        'bg-gray-400 text-white hover:bg-gray-500 dark:bg-slate-800 dark:hover:bg-slate-900' => Helpers::isGithubTheme()
    ])
    ->merge() }}
>
    {{ $slot }}
</div>
