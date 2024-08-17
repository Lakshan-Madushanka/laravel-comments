<div>
    <form wire:submit.prevent="create" class="w-full" method="POST">
        <x-honeypot wire:model="honeyPostData" />

        @if ($guestMode)
            <div class="flex flex-col gap-x-8 sm:flex-row">
                <div class="flex w-full flex-col">
                    <x-comments::input wire:model="guest_name" placeholder="{{__('Reply as')}}" />
                    <div class="min-h-6">
                        @if ($errors->has('guest_name'))
                            <span class="align-top text-xs text-red-500 sm:text-sm">
                                {{ __($errors->first('guest_name')) }}
                            </span>
                        @endif
                    </div>
                </div>
                @if (config('comments.reply.email_enabled'))
                    <div class="flex w-full flex-col">
                        <x-comments::input wire:model="guest_email" type="email" placeholder="{{__('Email')}}" />
                        <div class="min-h-6">
                            @if ($errors->has('guest_email'))
                                <span class="align-top text-xs text-red-500 sm:text-sm">
                                    {{ __($errors->first('guest_email')) }}
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
                x-data="successMsg"
                @reply-created-{{ $comment->getKey() }}.window="set(true, $event)"
            >
                <span x-show="show" x-transition class="align-top text-xs text-green-500 sm:text-sm">
                    @if ($approvalRequired)
                        {{ __('Reply created and will be displayed once approved.') }}
                    @else
                        {{ __('Reply created.') }}
                    @endif
                </span>
            </div>
        </div>
        @if (! $limitExceeded)
            @if ($loginRequired)
                <div>
                    <span>
                        {{ __('Please') }}
                        <x-comments::link
                            wire:click.prevent="redirectToLogin(window.location.href)"
                            class="font-bold text-blue-600"
                        >
                            {{ __('login') }}
                        </x-comments::link>
                        {{ __('to reply !') }}
                    </span>
                </div>
            @else
                <div class="flex gap-x-2">
                    <x-comments::button loadingTarget="create" class="w-full sm:w-auto" size="sm">
                        {{ __('Create') }}
                    </x-comments::button>
                    <x-comments::button
                        wire:click="discard"
                        loadingTarget="discard"
                        type="button"
                        class="w-full sm:w-auto"
                        size="sm"
                    >
                        {{ __('Discard') }}
                    </x-comments::button>
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

    @script
        <script>
            Alpine.data('successMsg', () => ({
                show: false,
                timeout: 2000,

                set(show, event) {
                    if (event.detail.editorId !== $wire.editorId) {
                        return;
                    }
                    this.show = show;
                    setTimeout(() => {
                        this.show = false;
                    }, this.timeout);
                },
            }));
        </script>
    @endscript
</div>
