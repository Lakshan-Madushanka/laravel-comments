@props(['loadingTarget' => ''])

<div
    x-show="show"
    x-transition
    class='z-10 bg-gray-900/25 z-10 w-full h-screen h-svh overflow-auto flex justify-center items-center fixed top-0 start-0 dark:bg-gray-600/85'
>
    <div
        @keydown.escape.window="show=false"
        @click.outside="show=false"
        {{$attributes->class(['z-20 rounded-sm border border-gray-200 bg-white dark:bg-slate-800 dark:border-black shadow-lg mx-2 w-full max-h-[50svh] lg:max-h-[65svh] overflow-auto sm:w-auto sm:min-w-lg'])}}
    >
        <div @click="show=false" class="flex w-full dark:bg-black cursor-pointer justify-end p-1">
            <x-commenter::icons.close />
        </div>

        <div>
            {{ $slot }}
        </div>

        <div wire:loading.flex class="mb-4 flex w-full items-center justify-center">
            <x-commenter::spin wire:loading.inline-block wire:target="{{$loadingTarget}}" class="text-black!" />
        </div>
    </div>
</div>
