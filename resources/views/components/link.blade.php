@props(['route' => '#'])

<a
    wire:navigate
    href="{{$route}}"
    {{$attributes->merge(["class" => "font-medium text-blue-600 dark:text-blue-500 hover:underline"])}}
>
    {{$slot}}
</a>
