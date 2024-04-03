@props(['loadingTarget' => '', 'dirtyTarget' => ''])

<button
    wire:loading.remove
    wire:dirty.remove.attr="disabled"
    wire:dirty.class="cursor-pointer"
    @if($dirtyTarget)
        wire:target="{{$dirtyTarget}}"
    @endif
        disabled
    type="button"
    {{$attributes->merge(['class' => 'py-2 px-3 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600 cursor-not-allowed'])}}
>
    {{$slot}}
</button>

<button
    wire:loading.inline-flex
    @if($dirtyTarget)
        wire:loading="{{$loadingTarget}}"
    @endif
    type="button"
    {{$attributes->merge(['class' => 'py-2 px-3 inline-flex justify-between items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600'])}}
>
     <span>{{$slot}}</span>
    <x-comments::spin/>
</button>
