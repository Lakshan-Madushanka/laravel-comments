@props(['loadingTarget' => '', 'dirtyTarget' => '', 'type' => 'submit'])

@if($type === 'submit')
    <button
        wire:loading.remove
        wire:dirty.remove.attr="disabled"
        wire:dirty.class="cursor-pointer"
        @if($dirtyTarget)
            wire:target="{{$dirtyTarget}}"
        @endif
        disabled
        type="button"
        {{$attributes->merge(['class' => 'py-1 px-2 text-sm lg:text-base lg:py-2 lg:px-3 py-2 px-3 inline-flex items-center gap-x-2 text-sm font-semibold rounded border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600 cursor-not-allowed'])}}
    >
        {{$slot}}
    </button>

    <button
        wire:loading.inline-flex
        @if($loadingTarget)
            wire:target="{{$loadingTarget}}"
        @endif
        type="button"
        disabled
        {{$attributes->merge(['class' => 'py-1 px-2 text-sm lg:text-base lg:py-2 lg:px-3  py-2 px-3 inline-flex justify-between items-center gap-x-2 text-sm font-semibold rounded border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600'])}}
    >
        <span>{{$slot}}</span>
        <x-comments::spin/>
    </button>
@endif

@if($type === 'button')
    <button
        wire:loading.remove
        @if($loadingTarget)
            wire:target="{{$loadingTarget}}"
        @endif
        type="button"
        {{$attributes->merge(['class' => 'py-1 px-2 text-sm lg:text-base lg:py-2 lg:px-3 inline-flex items-center gap-x-2 text-sm font-semibold rounded border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600'])}}
    >
        {{$slot}}
    </button>

    <button
        wire:loading.inline-flex
        @if($loadingTarget)
            wire:target="{{$loadingTarget}}"
        @endif
            disabled
        type="button"
        {{$attributes->merge(['class' => 'py-1 px-2 text-sm lg:text-base lg:py-2 lg:px-3 py-2 px-3 inline-flex items-center gap-x-2 text-sm font-semibold rounded border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600 cursor-not-allowed'])}}
    >
        <span>{{$slot}}</span>
        <x-comments::spin/>
    </button>
@endif
