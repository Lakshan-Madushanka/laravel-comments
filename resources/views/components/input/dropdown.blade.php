<div {{ $attributes->merge(['class' => 'inline-flex']) }}>
    <div
        class="duration min-w-60 divide-gray-200 rounded-xl shadow-xl transition-[opacity,margin] hover:!bg-[{{ config('commenter.hover_color') }}] dark:divide-neutral-700 dark:border dark:border-neutral-700 dark:bg-neutral-800"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="hs-dropdown-with-dividers"
        @style(['background: ' . config('commenter.bg_primary_color')])
    >
        {{ $slot }}
    </div>
</div>
