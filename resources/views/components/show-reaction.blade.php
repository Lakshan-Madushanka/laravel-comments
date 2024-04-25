@props(['key', 'reactions', 'lastReactedUserName', 'comment'])

<div
        x-data="{isReacted: $wire.reactions['{{$key}}']['reacted'], showUsers: false}"
        @mouseleave="showUsers=false"
        class="flex flex-row items-center justify-center p-1"
>
    <div
            @if(Auth::check())
                @mouseover="
                 if($wire.reactions['{{$key}}']['count'] > 0 && !showUsers) {
                     showUsers = true;
                     $wire.lastReactedUser('{{$key}}')
                 }
                 "
            @endif
            @click="
                    isReacted = !isReacted
                    $wire.handle('{{$key}}')
                    $wire.lastReactedUser('{{$key}}')
                    showUsers=false
                    "
            class="pr-1 cursor-pointer"
            wire:loading.class="cursor-not-allowed"
            wire:target="lastReactedUser"
    >
        @if($reactions[$key]['reacted'])
            <x-dynamic-component component="comments::icons.{{$key}}" :fill="$this->fillColor($key)" />
        @else
            <x-dynamic-component component="comments::icons.{{$key}}" />
        @endif
    </div>

    <span class="text-sm">{{$reactions[$key]['count']}}</span>

    <x-comments::show-reacted-users
            :lastReactedUserName="$lastReactedUserName"
            :reactions="$reactions"
            :key="$key"
            :comment="$comment"
            class="left-[-12rem] bottom-[-3.8rem]"
            wrapperClass="left-[-2rem] bottom-[-3rem]"
    />
</div>
