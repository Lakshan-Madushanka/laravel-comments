@props([
    'lastReactedUserName',
    'reactions',
    'key',
    'message',
    'authMode',
])

<div class="absolute" x-show="showUsers" x-transition>
    <div class="{{ $attributes->get('wrapperClass') }} absolute! p-1" wire:loading wire:target="lastReactedUser">
        <x-commenter::spin />
    </div>

    @if ($authMode && $lastReactedUserName)
        <div
            {{ $attributes->merge(['class' => 'absolute z-10 flex items-center inset-0  bottom-[-3.8rem] -start-8 w-[max-content]']) }}
            wire:loading.remove
            wire:target="lastReactedUser"
        >
            <div class="flex flex-col rounded-sm border border-gray-200 bg-white dark:bg-black! p-1 text-sm shadow-sm  dark:border-slate-700">
                <span>
                    {{ Str::limit($lastReactedUserName, 10) }}
                    @if ($reactions[$key]['count'] > 1)
                        {{ __('and') }} {{ $reactions[$key]['count'] - 1 }} {{ __('other') }}
                    @endif

                    {{ __('reacted') }}.
                </span>
                @if ($reactions[$key]['count'] > 1)
                    <span
                        wire:click="loadReactedUsers('{{ $key }}')"
                        @click="$dispatch('show-user-list', {id: '{{ $message->getKey() }}', type: '{{ $key }}'})"
                        class="w-full cursor-pointer pb-1 text-center"
                    >
                        <x-commenter::link type="popup">{{ __('show all') }}</x-commenter::link>
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>
