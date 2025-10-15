<div class="p-2 flex gap-x-2 items-center w-full">
    <x-commenter::pencil height="13" width="13"
                         strokeColor="{{config('commenter.primary_color')}}" />
    <span wire:click="pin" class="text-xs hover:!no-underline sm:text-sm">
        @if($msg->is_pinned)
            {{__('Unpin')}}
        @else
        {{__('Pin')}}
        @endif
    </span>
</div>
