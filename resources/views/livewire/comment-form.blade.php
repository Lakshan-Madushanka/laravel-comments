<div>
    <form class="w-full">
        <x-honeypot wire:model="honeyPostData"/>

        @if($model->guestModeEnabled())
            <div class="flex gap-x-8 flex-col sm:flex-row">
                <div class="flex flex-col w-full">
                    <x-comments::input wire:model="guest_name" :shouldDisable="$limitExceeded"
                                       placeholder="Comment as"/>
                    <div class="min-h-6">
                        @if($errors->has('guest_name'))
                            <span
                                    class="text-red-500 align-top text-xs sm:text-sm">{{$errors->first('guest_name')}}</span>
                        @endif
                    </div>
                </div>
                @if(config('comments.guest_mode.email_enabled'))
                    <div class="flex flex-col w-full">
                        <x-comments::input wire:model="guest_email" :shouldDisable="$limitExceeded" type="email"
                                           placeholder="Email"/>
                        <div class="min-h-6">
                            @if($errors->has('guest_email'))
                                <span
                                        class="text-red-500 align-top text-xs sm:text-sm">{{$errors->first('guest_email')}}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif
        <div>
            <div wire:ignore id="{{config('comments.editor_toolbar_id')}}"></div>
            <div wire:ignore id="{{config('comments.editor_id')}}" class="w-full"></div>
        </div>
        <div class="min-h-6">
            <div x-cloak x-data="successMsg" @comment-created.window="set(true)">
                <span x-show="show"
                      x-transition
                      class="text-green-500 text-xs sm:text-sm align-top"
                >
                    Comment Created
                </span>
            </div>
            <div>
                @if($errors->has('text'))
                    <span class="text-red-500 align-top text-xs sm:text-sm"> {{$errors->first('text')}}</span>
                @endif
            </div>
        </div>
        @if(! $limitExceeded)
            @if($loginRequired)
                <div>
                <span>
                    Please
                    <x-comments::link
                            wire:click.prevent="redirectToLogin(window.location.href)"
                            class="text-blue-600 font-bold"
                    >
                        login
                    </x-comments::link>
                    to comment !
                </span>
                </div>
            @else
                <x-comments::button class="w-full sm:w-auto" wire:click="create">Create</x-comments::button>
            @endif
        @else
            <div>
                <span class="text-red-500">Allowed comment limit ({{$model->getCommentLimit()}}) exceeded !</span>
            </div>
        @endif
    </form>

    @script
    <script>
        let editorConfig = @js(config('comments.editor_config'));
        const quill = new Quill('#{{config('comments.editor_id')}}', editorConfig);

        const editorElm = document.querySelector('#{{config('comments.editor_id')}} .ql-editor');
        const toolbarParentElm = document.querySelector('#{{config('comments.editor_toolbar_id') }}');
        const toolbarElm = document.querySelector('.ql-toolbar');

        toolbarParentElm.append(toolbarElm);

        if (!$wire.LoginRequired || $wire.limitExceeded) {
            quill.disable();
        }

        quill.on('text-change', (delta, oldDelta, source) => {
            let html = editorElm.innerHTML;
            if (html === '<p><br></p>') {
                $wire.text = '';
                return
            }
            $wire.text = html;
        });

        $wire.on('comment-created', function () {
            quill.setText($wire.text)
        });

        Alpine.data('successMsg', () => ({
            show: false,
            timeout: 2000,

            set(show) {
                this.show = show;
                setTimeout(() => {
                    this.show = false;
                }, this.timeout)
            }
        }));
    </script>
    @endscript
</div>
