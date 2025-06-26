<div {{$attributes->merge(['class' => 'inline-flex'])}}>
    <div
        class="transition-[opacity,margin] hover:!bg-[{{config('commenter.hover_color')}}] duration min-w-60 shadow-xl rounded-xl divide-gray-200 dark:bg-neutral-800 dark:border dark:border-neutral-700 dark:divide-neutral-700" role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-with-dividers"
        @style([
            'background: ' . config('commenter.bg_primary_color')
        ])
    >
        {{$slot}}
    </div>
</div>
