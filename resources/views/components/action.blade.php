<span
    {{ $attributes->merge(['class' => 'font-medium hover:underline cursor-pointer']) }}
    @style([
        'color: ' . config('comments.primary_color'),
    ])
>
    {{ $slot }}
</span>
