@php use LakM\Comments\GuestModeRateLimiter; @endphp

<div @logout.window="$wire.$refresh()">
    <div class="lakm_commenter w-full" method="POST">
        <x-honeypot wire:model="honeyPostData" />

        @if($guestEmailVerified)
            <div x-data="{show:true}">
                <x-comments::modal>
                    <div class="text-green-600 text-lg text-center font-bold p-4">Your email verified successfully!</div>
                </x-comments::modal>
            </div>
        @endif
        @if ($model->guestModeEnabled() && !$this->secureGuestMode->enabled())
            <div class="flex flex-col gap-x-8 sm:flex-row">
                <div class="flex w-full flex-col">
                    <x-comments::input
                        wire:model="name"
                        :shouldDisable="$limitExceeded"
                        placeholder="{{__('Comment as')}}"
                    />
                    <div class="min-h-6">
                        @if ($errors->has('name'))
                            <span class="align-top text-xs text-red-500 sm:text-sm">
                                {{ __($errors->first('name')) }}
                            </span>
                        @endif
                    </div>
                </div>
                @if (config('comments.guest_mode.email_enabled'))
                    <div class="flex w-full flex-col">
                        <x-comments::input
                            wire:model="email"
                            :shouldDisable="$limitExceeded"
                            type="email"
                            placeholder="{{__('Email')}}"
                        />
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
            <livewire:comments-editor wire:model="text" :$editorId :$guestModeEnabled :$disableEditor />
        </div>

        <div class="min-h-6">
            <div x-cloak x-data="message(@js($editorId))" @comment-created.window="show($event.detail.id)">
                <span x-show="showMsg" x-transition class="align-top text-xs text-green-500 sm:text-sm">
                    @if ($approvalRequired)
                        {{ __('Comment created and will be displayed once approved.') }}
                    @else
                        {{ __('Comment created.') }}
                    @endif
                </span>
            </div>
            <div>
                @if ($errors->has('text'))
                    <span class="align-top text-xs text-red-500 sm:text-sm">{{ __($errors->first('text')) }}</span>
                @endif
            </div>
        </div>
        @if (! $limitExceeded)
            @if (!$this->guestModeEnabled && $loginRequired)
                <div>
                    <span>
                        {{ __('Please') }}
                        <x-comments::link
                            wire:click.prevent="redirectToLogin(window.location.href)"
                            class="font-bold text-blue-600"
                        >
                            {{ __('login') }}
                        </x-comments::link>
                        {{ __('to comment !') }}
                    </span>
                </div>
            @elseif($verifyLinkSent)
                <span class="text-green-400">Verify link was sent to your email address</span>
            @elseif(!$this->secureGuestMode->allowed())
                <div x-data="{showEmailField: false}" id="verify-email-button">
                    <span x-show="!showEmailField" x-transition>
                        {{ __('Please') }}
                        <x-comments::link
                            @click="showEmailField=true"
                            class="font-bold text-blue-600"
                            type="button"
                        >
                            {{ __('verify your email') }}
                        </x-comments::link>
                        {{ __('to comment !') }}
                    </span>

                    <div x-show="showEmailField" x-transition class="flex flex-col gap-y-2">
                        <div class="flex flex-col gap-x-8 sm:flex-row">
                            <div class="flex w-full flex-col">
                                <x-comments::input
                                    wire:model="name"
                                    placeholder="{{__('Comment as')}}"
                                />
                                <div class="min-h-6">
                                    @if ($errors->has('name'))
                                        <span class="align-top text-xs text-red-500 sm:text-sm">
                                         {{ __($errors->first('name')) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex w-full flex-col">
                                <x-comments::input
                                    wire:model="email"
                                    type="email"
                                    placeholder="{{__('Email')}}"
                                />
                                <div class="min-h-6">
                                    @if ($errors->has('email'))
                                        <span class="align-top text-xs text-red-500 sm:text-sm">
                                            {{ __($errors->first('email')) }}
                                         </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if(!$rateLimitExceeded)
                            <div wire:click="sendVerifyLink(window.location.href)">
                                <x-comments::button size="sm"  loadingTarget="sendVerifyLink">
                                    Send Link
                                </x-comments::button>
                            </div>
                        @else
                            <div x-cloak x-data="countdown(@js(GuestModeRateLimiter::$decaySeconds))" @counter-finished.window="$wire.set('rateLimitExceeded', false)">
                                <span x-init="start" class="text-red-600">Max limit exceeded ({{GuestModeRateLimiter::$maxAttempts}}) try again in: <span x-text="count"></span></span>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <x-comments::button wire:click="create" loadingTarget="create" class="w-full sm:w-auto">
                    {{ __('Create') }}
                </x-comments::button>
            @endif
        @else
            <div>
                <span class="text-red-500">
                    {{ __('Allowed comment limit') }} ({{ $model->getCommentLimit() }}) {{ __('exceeded !') }}
                </span>
            </div>
        @endif
    </div>
</div>
