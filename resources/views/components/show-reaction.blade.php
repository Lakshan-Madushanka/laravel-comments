@props([
    "key",
    "reactions",
    "lastReactedUserName",
    "comment",
    'authMode'
])

<div
    x-data="{
        isReacted: $wire.reactions['{{ $key }}']['reacted'],
        showUsers: false,
    }"
    @mouseleave="showUsers=false"
    class="flex flex-row items-center justify-center bg-gray-300 px-1 py-[2px] rounded-md hover:bg-gray-400"
>
    <div
        @if ($authMode)
            @mouseover="
                 if($wire.reactions['{{ $key }}']['count'] > 0 && !showUsers) {
                     showUsers = true;
                     $wire.lastReactedUser('{{ $key }}')
                 }
                 "
        @endif
        @click="
                isReacted = !isReacted
                $wire.handle('{{ $key }}')
                $wire.lastReactedUser('{{ $key }}')
                showUsers=false
                "
        class="cursor-pointer flex items-center gap-x-1"
        wire:loading.class="cursor-not-allowed"
        wire:target="lastReactedUser"
        title="{{$key}}"
    >
        @if ($reactions[$key]["reacted"])
            <x-dynamic-component component="comments::icons.{{$key}}" :fill="$this->fillColor($key)" />
        @else
            <x-dynamic-component component="comments::icons.{{$key}}" />
        @endif

        <span class="text-sm">{{ $reactions[$key]["count"] }}</span>
    </div>


    <x-comments::show-reacted-users
        :$lastReactedUserName
        :$reactions
        :$key
        :$comment
        :$authMode
        class="bottom-[-3.8rem] left-[-12rem]"
        wrapperClass="left-[-2rem] bottom-[-3rem]"
    />
</div>
