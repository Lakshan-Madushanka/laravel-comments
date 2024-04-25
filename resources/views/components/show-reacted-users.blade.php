@props(['lastReactedUserName', 'reactions', 'key', 'comment'])

<div
        class="relative"
        x-show="showUsers"
        x-transition
>
    <div
            class="p-1 absolute {{$attributes->get('wrapperClass')}}"
            wire:loading
            wire:target="lastReactedUser"
    >
        <x-comments::spin class="!text-black" />
    </div>

    @if(Auth::check() && $lastReactedUserName)
        <div
                {{$attributes->merge(['class' => 'absolute flex items-end h-16 left-[-12rem] bottom-[-3.8rem] min-w-[12rem]'])}}
                wire:loading.remove
                wire:target="lastReactedUser"
        >
            <div class="flex flex-col w-full text-sm bg-gray-200 border rounded p-1">
                <span>
                  {{Str::limit($lastReactedUserName, 5)}}
                    @if($reactions[$key]['count'] > 1)
                        and {{$reactions[$key]['count'] - 1}} other
                    @endif
                    reacted.
                </span>
                @if($reactions[$key]['count'] > 1)
                    <span
                            wire:click="loadReactedUsers('{{$key}}')"
                            @click="$dispatch('show-user-list', {id: '{{$comment->getKey()}}', type: '{{$key}}'})"
                            class="w-full text-center cursor-pointer"
                    >
                        <x-comments::link type="popup">show all</x-comments::link>
                     </span>
                @endif
            </div>
        </div>
    @endif
</div>
