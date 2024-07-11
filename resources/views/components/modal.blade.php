@props(['loadingTarget' => ''])

<div
    x-show="show"
    x-transition
    {{ $attributes->merge(['class' => 'z-10 w-full h-full overflow-auto flex justify-center items-center fixed top-0 left-0']) }}
>
    <div
        @keydown.escape.window="show=false"
        @click.outside="show=false"
        class="rounded border border-gray-200 bg-white shadow-lg mx-2 w-full max-h-[50svh] lg:max-h-[65svh] overflow-auto sm:w-auto sm:min-w-96"
    >
        <div @click="show=false" class="flex w-full cursor-pointer justify-end p-1">
            <x-comments::icons.close />
        </div>

        <div>
            {{ $slot }}
        </div>

        <div class="mb-4 flex w-full items-center justify-center">
            <x-comments::spin wire:loading.inline-block wire:target="{{$loadingTarget}}" class="!text-black" />
        </div>
    </div>
</div>
