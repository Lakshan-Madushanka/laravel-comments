@props(['loadingTarget' => ''])

<div
    x-show="show"
    x-transition
    {{$attributes->merge(["class" => "w-full h-full flex justify-center items-center fixed top-0 left-0"])}}
>
    <div @keydown.escape.window="show=false" @click.outside="show=false" class="bg-white border shadow-lg lg:min-w-96 rounded">
        <div @click="show=false" class="flex w-full justify-end cursor-pointer p-1">
            <x-comments::icons.close/>
        </div>

        <div>
            {{$slot}}
        </div>

        <div class="w-full flex justify-center items-center mb-4">
            <x-comments::spin wire:loading.inline-block wire:target="{{$loadingTarget}}" class="!text-black"/>
        </div>
    </div>
</div>
