@props([
    'name',
    'icon' => null,
])
<div class="flex cursor-pointer items-center gap-x-2 space-y-0.5 p-1">
    <span
        class="flex w-full items-center gap-x-3.5 rounded-lg px-3 py-2 text-sm font-bold text-gray-800 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700"
    >
        @if ($icon)
            <x-dynamic-component :component="'commenter::icons.' . $icon" />
        @endif

        {{ $name }}
    </span>
</div>
