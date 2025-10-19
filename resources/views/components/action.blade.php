<span
    {{ $attributes->merge(['class' => 'font-medium hover:underline cursor-pointer dark:text-white!']) }}
    @style([
        'color: ' . config('commenter.primary_color'),
    ])
>
    {{ $slot }}
</span>
