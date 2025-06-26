<div>
    <form wire:submit.prevent="create" class="w-full" method="POST">
        <x-honeypot wire:model="honeyPostData" />

        @if ($guestMode && !$this->secureGuestMode->enabled())
            <div class="flex flex-col gap-x-8 sm:flex-row">
                <div class="flex w-full flex-col">
                    <x-commenter::input wire:model="name" :shouldDisable="$limitExceeded" placeholder="{{__('Reply as')}}" />
                    <div class="min-h-6">
                        @if ($errors->has('name'))
                            <span class="align-top text-xs text-red-500 sm:text-sm">
                                {{ __($errors->first('name')) }}
                            </span>
                        @endif
                    </div>
                </div>
                @if (config('commenter.reply.email_enabled'))
                    <div class="flex w-full flex-col">
                        <x-commenter::input wire:model="email" :shouldDisable="$limitExceeded" type="email" placeholder="{{__('Email')}}" />
                        <div class="min-h-6">
                            @if ($errors->has('email'))
                                <span class="align-top text-xs text-red-500 sm:text-sm">
                                    {{ __($errors->first('email')) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div>
            <livewire:comments-editor wire:model="text" :$editorId :guestModeEnabled="$guestMode" />
        </div>

        <div class="min-h-6">
            <div>
                @if ($errors->has('text'))
                    <span class="align-top text-xs text-red-500 sm:text-sm">{{ __($errors->first('text')) }}</span>
                @endif
            </div>
            <div
                x-cloak
                x-data="message(@js($editorId))"
                @reply-created-{{ $message->getKey() }}.window="show($event.detail.editorId)"
            >
                <span x-show="showMsg" x-transition class="align-top text-xs text-green-500 sm:text-sm">
                    @if ($approvalRequired)
                        {{ __('Reply created and will be displayed once approved.') }}
                    @else
                        {{ __('Reply created.') }}
                    @endif
                </span>
            </div>
        </div>
        @if (! $limitExceeded)
            @if (!$guestMode && $loginRequired)
                <div>
                    <span>
                        {{ __('Please') }}
                        <x-commenter::link
                            wire:click.prevent="redirectToLogin(window.location.href)"
                            class="font-bold text-blue-600"
                        >
                            {{ __('login') }}
                        </x-commenter::link>
                        {{ __('to reply !') }}
                    </span>
                </div>
            @elseif(!$this->secureGuestMode->allowed())
                <x-commenter::link type="a" route="#verify-email-button">{{ __('Please verify your email to reply') }}</x-commenter::link>
            @else
                <div class="flex gap-x-2">
                    <x-commenter::button loadingTarget="create" class="w-full sm:w-auto" size="sm">
                        {{ __('Create') }}
                    </x-commenter::button>
                    <x-commenter::button
                        wire:click="discard"
                        loadingTarget="discard"
                        type="button"
                        class="w-full sm:w-auto"
                        size="sm"
                    >
                        {{ __('Discard') }}
                    </x-commenter::button>
                </div>
            @endif
        @else
            <div>
                <span class="text-red-500">
                    {{ __('Allowed reply limit') }} ({{ $this->replyLimit() }}) {{ __('exceeded !') }}
                </span>
            </div>
        @endif
    </form>
</div>
