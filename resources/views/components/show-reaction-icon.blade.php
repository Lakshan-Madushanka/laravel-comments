@php use LakM\Commenter\Helpers; @endphp

@props([
    'type',
    'isFilled'
])

<span>
    @if($isFilled)
        @if(Helpers::getReactionEmoji($type, true))
            {{Helpers::getReactionEmoji($type, true)}}
        @else
            <x-dynamic-component component="commenter::icons.{{$type}}" :fill="$this->fillColor($type)" />
        @endif
    @else
        @if(Helpers::getReactionEmoji($type, false))
            {{Helpers::getReactionEmoji($type, false)}}
        @else
            <x-dynamic-component component="commenter::icons.{{$type}}" />
        @endif
    @endif
</span>

