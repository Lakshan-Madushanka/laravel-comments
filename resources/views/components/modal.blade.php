@props(['loadingTarget' => ''])

<div
    x-show="show"
    x-transition
    class="fixed start-0 top-0 z-10 z-10 flex h-screen h-svh w-full items-center justify-center overflow-auto bg-gray-900/25 dark:bg-gray-600/85"
>
    <div
        @keydown.escape.window="show=false"
        @click.outside="show=false"
        {{ $attributes->class(['z-20 mx-2 max-h-[50svh] w-full overflow-auto rounded border border-gray-200 bg-white shadow-lg sm:w-auto sm:min-w-[32rem] lg:max-h-[65svh] dark:border-black dark:bg-slate-800']) }}
    >
        <div @click="show=false" class="flex w-full cursor-pointer justify-end p-1 dark:bg-black">
            <x-commenter::icons.close />
        </div>

        <div>
            {{ $slot }}
        </div>

        <div wire:loading.flex class="mb-4 flex w-full items-center justify-center">
            <x-commenter::spin wire:loading.inline-block wire:target="{{$loadingTarget}}" class="!text-black" />
        </div>
    </div>
</div>
