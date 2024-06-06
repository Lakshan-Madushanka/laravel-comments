@props([
    'lastReactedUserName',
    'reactions',
    'key',
    'comment',
    'authMode',
])

<div class="absolute" x-show="showUsers" x-transition>
    <div class="{{ $attributes->get('wrapperClass') }} !absolute p-1" wire:loading wire:target="lastReactedUser">
        <x-comments::spin class="!text-black" />
    </div>

    @if ($authMode && $lastReactedUserName)
        <div
            {{ $attributes->merge(['class' => 'absolute z-10 flex items-center h-16 left-[-12rem] bottom-[-3.8rem] min-w-[14rem]']) }}
            wire:loading.remove
            wire:target="lastReactedUser"
        >
            <div class="flex w-full flex-col rounded border border-gray-200 bg-white p-1 text-sm shadow">
                <span>
                    {{ Str::limit($lastReactedUserName, 10) }}
                    @if ($reactions[$key]['count'] > 1)
                        and {{ $reactions[$key]['count'] - 1 }} other
                    @endif

                    reacted.
                </span>
                @if ($reactions[$key]['count'] > 1)
                    <span
                        wire:click="loadReactedUsers('{{ $key }}')"
                        @click="$dispatch('show-user-list', {id: '{{ $comment->getKey() }}', type: '{{ $key }}'})"
                        class="w-full cursor-pointer pb-1 text-center"
                    >
                        <x-comments::link type="popup">show all</x-comments::link>
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>
