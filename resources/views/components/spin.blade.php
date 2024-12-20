<span
    {{ $attributes->merge(['class' => 'inline-block size-4 animate-spin rounded-full border-[3px] border-current border-t-transparent text-white']) }}
    @style([
         'color: ' . config('comments.primary_color') . ';'
     ])
    role="status"
    aria-label="loading"
>
    <span class="sr-only">Loading...</span>
</span>
