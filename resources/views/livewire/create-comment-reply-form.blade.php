<div>
    <form wire:submit.prevent="create" class="w-full" method="POST">
        <x-honeypot wire:model="honeyPostData" />

        @if ($guestMode)
            <div class="flex flex-col gap-x-8 sm:flex-row">
                <div class="flex w-full flex-col">
                    <x-comments::input wire:model="guest_name" placeholder="Comment as" />
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
                        <x-comments::input wire:model="guest_email" type="email" placeholder="Email" />
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

        <div wire:ignore class="relative">
            <div id="{{ $editorId }}" class="min-h-32 rounded rounded-t-none"></div>
            <div id="{{ $toolbarId }}" class="w-full"></div>

            <div
                @click.outside="$wire.dispatch('user-not-mentioned.' + '{{ $editorId }}')"
                class="absolute bottom-[12rem] left-0 z-10 w-full"
            >
                <livewire:comments-user-list :guestModeEnabled="$guestMode" :$editorId />
            </div>
        </div>
        <div class="min-h-6">
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
                <div class="flex gap-x-2">
                    <x-comments::button loadingTarget="create" class="w-full sm:w-auto" size="sm">
                        Create
                    </x-comments::button>
                    <x-comments::button
                        wire:click="discard"
                        loadingTarget="discard"
                        type="button"
                        class="w-full sm:w-auto"
                        size="sm"
                    >
                        Discard
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

            $wire.on('reply-created', function () {
                quill.setText($wire.text);
            });

            $wire.on('reply-discarded', function () {
                quill.setText('');
            });

            quill.on('text-change', () => handleEditorTextChange(editorElm, $wire));
            Livewire.on('user-selected.' + $wire.editorId, () =>
                window.onMentionedUserSelected(event, quill, editorElm)
            );
        </script>
    @endscript
</div>
