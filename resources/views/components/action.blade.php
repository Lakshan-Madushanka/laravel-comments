<span
    {{ $attributes->merge(['class' => 'font-medium hover:underline cursor-pointer']) }}
    @style([
        'color: ' . config('commenter.primary_color'),
    ])
>
    {{ $slot }}
</span>
