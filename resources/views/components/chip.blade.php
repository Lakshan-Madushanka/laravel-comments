@php use LakM\Comments\Helpers; @endphp
<div {{ $attributes
    ->class([
        'px-2 py-1 rounded cursor-pointer transition ml-[-6px] sm:ml-[2px] text-nowrap',
        'border hover:bg-gray-300' => Helpers::isDefaultTheme(),
        'bg-gray-400 text-white hover:bg-gray-500' => Helpers::isGithubTheme()
    ])
    ->merge() }}
>
    {{ $slot }}
</div>
