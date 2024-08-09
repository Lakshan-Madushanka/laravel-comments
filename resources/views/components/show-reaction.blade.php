@php use Illuminate\Support\Number; @endphp
@props([
    "key",
    "reactions",
    "lastReactedUserName",
    "comment",
    "authMode",
    "loginRequired",
])

<div
    x-data="{
        isReacted: $wire.reactions['{{ $key }}']['reacted'],
        showUsers: false,
    }"
    @mouseleave="showUsers=false"
    @class([
          "flex flex-row items-center justify-center rounded-md px-1 py-[2px]",
          "border hover:bg-gray-200" => config('comments.theme') === 'default',
          "bg-gray-300 hover:bg-gray-400" => config('comments.theme') === 'github'
    ])
>
    <div
        @if ($authMode)
            @mouseover="
                 if(@js(! $loginRequired) && $wire.reactions['{{ $key }}']['count'] > 0 && !showUsers) {
                     showUsers = true;
                     $wire.lastReactedUser('{{ $key }}')
                 }
                 "
        @endif
        @click="
                if(@js($loginRequired)) {
                    $wire.redirectToLogin('window.location.ref')
                    return;
                }
                isReacted = !isReacted
                $wire.handle('{{ $key }}')
                $wire.lastReactedUser('{{ $key }}')
                showUsers=false
                "
        class="min-w-[2.2rem] flex cursor-pointer justify-between items-center gap-x-1"
        wire:loading.class="cursor-not-allowed"
        wire:target="lastReactedUser"
        title="{{ $key }}"
    >
        @if ($reactions[$key]["reacted"])
            <x-dynamic-component component="comments::icons.{{$key}}" :fill="$this->fillColor($key)"/>
        @else
            <x-dynamic-component component="comments::icons.{{$key}}"/>
        @endif

        <span class="text-sm">{{ Number::abbreviate($reactions[$key]["count"]) }}</span>
    </div>

    <x-comments::show-reacted-users
        :$lastReactedUserName
        :$reactions
        :$key
        :$comment
        :$authMode
        class="!bottom-[-4.8rem] left-[-12rem]"
        wrapperClass="left-[-0.8rem] bottom-[-3rem]"
    />
</div>
