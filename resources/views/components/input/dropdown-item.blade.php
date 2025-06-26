@props(['name', 'icon' => null])
<div class="p-1 space-y-0.5 flex items-center gap-x-2 cursor-pointer">
    <span class="flex w-full items-center gap-x-3.5 py-2 px-3 font-bold rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700">
        @if($icon)
            <x-dynamic-component  :component="'commenter::icons.' . $icon" />
        @endif
        {{$name}}
    </span>
</div>
