<div>
    <form class="w-full">
        <x-honeypot wire:model="honeyPostData" />

        @if ($model->guestModeEnabled())
            <div class="flex flex-col gap-x-8 sm:flex-row">
                <div class="flex w-full flex-col">
                    <x-comments::input
                        wire:model="guest_name"
                        :shouldDisable="$limitExceeded"
                        placeholder="Comment as"
                    />
                    <div class="min-h-6">
                        @if ($errors->has('guest_name'))
                            <span class="align-top text-xs text-red-500 sm:text-sm">
                                {{ __($errors->first('guest_name')) }}
                            </span>
                        @endif
                    </div>
                </div>
                @if (config('comments.guest_mode.email_enabled'))
                    <div class="flex w-full flex-col">
                        <x-comments::input
                            wire:model="guest_email"
                            :shouldDisable="$limitExceeded"
                            type="email"
                            placeholder="Email"
                        />
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

        <div wire:ignore>
            <div id="{{ $editorId }}" class="min-h-32 rounded rounded-t-none"></div>
            <div id="{{ $toolbarId }}" class="w-full"></div>
        </div>
        <div class="min-h-6">
            <div x-cloak x-data="successMsg" @comment-created.window="set(true, $event)">
                <span x-show="show" x-transition class="align-top text-xs text-green-500 sm:text-sm">
                    @if ($model->approvalRequired())
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
                        {{ __('to comment !') }}
                    </span>
                </div>
            @else
                <x-comments::button class="w-full sm:w-auto" wire:click="create">Create</x-comments::button>
            @endif
        @else
            <div>
                <span class="text-red-500">
                    {{ __('Allowed comment limit') }} ({{ $model->getCommentLimit() }}) {{ __('exceeded !') }}
                </span>
            </div>
        @endif
    </form>

    @script
        <script>
            let editorConfig = @js(config('comments.editor_config'));
            const quill = new Quill(`#${$wire.editorId}`, editorConfig);

            const editorElm = document.querySelector(`#${$wire.editorId} .ql-editor`);
            const toolbarParentElm = document.querySelector(`#${$wire.toolbarId}`);

            const toolbars = Array.from(document.querySelector('.ql-toolbar'));

            toolbarParentElm.append(toolbars.slice(-1));

            if (!$wire.LoginRequired || $wire.limitExceeded) {
                quill.disable();
            }

            quill.on('text-change', (delta, oldDelta, source) => {
                let html = editorElm.innerHTML;
                if (html === '<p><br></p>') {
                    $wire.text = '';
                    return;
                }
                $wire.text = html;
            });

            $wire.on('comment-created', function () {
                quill.setText($wire.text);
            });

            Alpine.data('successMsg', () => ({
                show: false,
                timeout: 2000,

                set(show, event) {
                    if (event.detail.id !== $wire.editorId) {
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
