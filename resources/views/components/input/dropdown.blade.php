<div {{$attributes->merge(['class' => 'inline-flex'])}}>
    <div
        class="transition-[opacity,margin] hover:!bg-[{{config('comments.hover_color')}}] duration min-w-60 shadow-xl rounded-xl divide-gray-200 dark:bg-neutral-800 dark:border dark:border-neutral-700 dark:divide-neutral-700" role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-with-dividers"
        @style([
            'background: ' . config('comments.bg_primary_color')
        ])
    >

        <div class="p-1 space-y-0.5">
            <a class="flex items-center gap-x-3.5 py-2 px-3 font-bold rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700" href="#">
                Upgrade License
            </a>
        </div>

        <div class="p-1 space-y-0.5">
            <a class="flex items-center gap-x-3.5 py-2 px-3 font-bold rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700" href="#">
                Upgrade License
            </a>
        </div>

    </div>
</div>
