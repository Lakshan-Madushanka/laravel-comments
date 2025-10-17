<div
    x-data="{modalIsOpen: false}"
    @action-confirmed="$wire.pin()"
    class="w-full"
>
    <div x-on:click="modalIsOpen=true" class="p-2 flex gap-x-2 items-center w-full">
        @if ($msg->is_pinned)
            <x-commenter::icons.unpin height="16" width="16" fill="{{config('commenter.primary_color')}}" />
            <span class="text-xs hover:!no-underline font-bold">
            {{__('Unpin')}}
        </span>
        @else
            <x-commenter::icons.pin height="16" width="16" fill="{{config('commenter.primary_color')}}" />
            <span class="text-xs hover:!no-underline font-bold">
            {{__('Pin')}}
        </span>
        @endif

    </div>

    <x-commenter::confirm-modal>
        <x-slot:body>
            @if(!$msg->is_pinned)
                {{__('This will appear at the top of your profile and replace any previously pinned post.')}}
            @else
                {{__('This will remove the pinned post from the top of your profile.')}}
            @endif
        </x-slot:body>
    </x-commenter::confirm-modal>
</div>

