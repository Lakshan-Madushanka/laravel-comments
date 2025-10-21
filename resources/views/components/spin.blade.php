@props(['color' => config('commenter.primary_color')])

<span
    {{ $attributes->merge([
        'class' => 'inline-block size-4 animate-spin rounded-full border-[3px] border-current border-t-transparent ' . 'text-' . $color . 'dark:!text-white'
        ])
    }}
    role="status"
    aria-label="loading"
>
    <span class="sr-only">{{ __('Loading') }}...</span>
</span>
