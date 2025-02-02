@props(['color' => config('comments.primary_color')])

<span
    {{ $attributes->merge(['class' => 'inline-block size-4 animate-spin rounded-full border-[3px] border-current border-t-transparent text-white']) }}
    @style([
         'color: ' . $color,
     ])
    role="status"
    aria-label="loading"
>
    <span class="sr-only">{{ __('Loading') }}...</span>
</span>
