<div>
    <form class="w-full">
        @if($model->guestModeEnabled())
            <div class="flex gap-x-8 flex-col sm:flex-row">
                <div class="flex flex-col w-full">
                    <x-comments::input wire:model="guest_name" placeholder="Comment as"/>
                    <div class="min-h-6">
                        @if($errors->has('guest_name'))
                            <span class="text-red-500 align-top text-xs sm:text-sm">{{$errors->first('guest_name')}}</span>
                        @endif
                    </div>
                </div>
                @if(config('comments.guest_mode.email_enabled'))
                    <div class="flex flex-col w-full">
                        <x-comments::input wire:model="guest_email" type="email" placeholder="Email"/>
                        <div class="min-h-6">
                            @if($errors->has('guest_email'))
                                <span class="text-red-500 align-top text-xs sm:text-sm">{{$errors->first('guest_email')}}</span>
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
            @if($errors->has('text'))
                <span class="text-red-500 text-sm align-top text-xs sm:text-sm"> {{$errors->first('text')}}</span>
            @endif
        </div>

        @if($loginRequired)
            <div>
                <span>
                    Please
                    <x-comments::link class="text-blue-600 font-bold"
                                      route="{{config('comments.login_route')}}">login</x-comments::link>
                    to comment !
                </span>
            </div>
        @else
            <x-comments::button class="w-full sm:w-auto" wire:click="create">Create</x-comments::button>
        @endif
        <div wire:click="create">test</div>
    </form>

    @script
    <script type="module" defer>
        const quill = new Quill('#{{config('comments.editor_id')}}', @js(config('comments.editor_config')));

        const editorElm = document.querySelector('#{{config('comments.editor_id')}} .ql-editor');
        const toolbarParentElm = document.querySelector('#{{config('comments.editor_toolbar_id') }}');
        const toolbarElm = document.querySelector('.ql-toolbar');

        toolbarParentElm.append(toolbarElm);

        if (!$wire.LoginRequired) {
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
    </script>
    @endscript
</div>
