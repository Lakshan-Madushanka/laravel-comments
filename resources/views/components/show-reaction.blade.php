@php use Illuminate\Support\Number;use LakM\Commenter\Helpers; @endphp
@props([
    "key",
    "reactions",
    "lastReactedUserName",
    "message",
    "authMode",
    "loginRequired",
    "secureGuestModeAllowed",
])

<div
    x-data="{
        isReacted: $wire.reactions['{{ $key }}']['reacted'],
        showUsers: false,
    }"
    @mouseleave="showUsers=false"
    @class([
          "flex flex-row items-center justify-center rounded-md px-1 py-[2px]",
          "hover:!bg-["  . config('commenter.hover_color') . "]" => Helpers::isGithubTheme() || Helpers::isModernTheme(),
          "border hover:!bg-gray-200 dark:border-slate-700 dark:hover:!bg-slate-900" => Helpers::isDefaultTheme(),
          "dark:!bg-slate-900 dark:hover:!bg-slate-600" => Helpers::isGithubTheme(),
          "!rounded-[1000px] !py-1 !px-2 dark:!bg-slate-900 dark:hover:!bg-slate-600" => Helpers::isModernTheme(),
    ])
    @style([
          'background: ' . config('commenter.bg_primary_color') => Helpers::isGithubTheme() || Helpers::isModernTheme(),
   ])
>
    <div
        @if ($authMode)
            @mouseover="
                 if(@js($authMode && ! $loginRequired) && $wire.reactions['{{ $key }}']['count'] > 0 && !showUsers) {
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

                if(@js(!$secureGuestModeAllowed)) {
                    window.location.hash = ''
                    window.location.hash = 'verify-email-button';
                    return;
                }

                isReacted = !isReacted
                $wire.handle('{{ $key }}')
                @if($authMode)
                 $wire.lastReactedUser('{{ $key }}')
                @endif
                showUsers=false
                "
        class="min-w-[2.2rem] flex cursor-pointer justify-between items-center gap-x-1"
        wire:loading.class="cursor-not-allowed"
        wire:target="lastReactedUser"
        title="{{ $key }}"
    >
        @if ($reactions[$key]["reacted"])
            <x-dynamic-component component="commenter::icons.{{$key}}" :fill="$this->fillColor($key)"/>
        @else
            <x-dynamic-component component="commenter::icons.{{$key}}"/>
        @endif

        <span class="text-sm">{{ Number::abbreviate($reactions[$key]["count"]) }}</span>
    </div>

    <x-commenter::show-reacted-users
        :$lastReactedUserName
        :$reactions
        :$key
        :$message
        :$authMode
        class="!bottom-[-4.8rem] start-[-12rem]"
        wrapperClass="start-[-0.8rem] bottom-[-3rem]"
    />
</div>
