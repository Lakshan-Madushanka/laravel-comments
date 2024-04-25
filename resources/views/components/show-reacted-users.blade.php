@props([
    'lastReactedUserName',
    'reactions',
    'key',
    'comment',
    'authMode'
])

<div class="relative" x-show="showUsers" x-transition>
    <div class="{{ $attributes->get('wrapperClass') }} absolute p-1" wire:loading wire:target="lastReactedUser">
        <x-comments::spin class="!text-black" />
    </div>

    @if ($authMode && $lastReactedUserName)
        <div
            {{ $attributes->merge(['class' => 'absolute flex items-end h-16 left-[-12rem] bottom-[-3.8rem] min-w-[12rem]']) }}
            wire:loading.remove
            wire:target="lastReactedUser"
        >
            <div class="flex w-full flex-col rounded border bg-gray-200 p-1 text-sm">
                <span>
                    {{ Str::limit($lastReactedUserName, 5) }}
                    @if ($reactions[$key]['count'] > 1)
                        and {{ $reactions[$key]['count'] - 1 }} other
                    @endif

                    reacted.
                </span>
                @if ($reactions[$key]['count'] > 1)
                    <span
                        wire:click="loadReactedUsers('{{ $key }}')"
                        @click="$dispatch('show-user-list', {id: '{{ $comment->getKey() }}', type: '{{ $key }}'})"
                        class="w-full cursor-pointer text-center"
                    >
                        <x-comments::link type="popup">show all</x-comments::link>
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>
