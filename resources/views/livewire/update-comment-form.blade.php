<div x-data="{ showMsg: false }">
    <div wire:ignore class="relative">
        <div id="{{ $editorId }}" class="min-h-32 rounded rounded-t-none"></div>
        <div id="{{ $toolbarId }}" class="w-full"></div>

        <div
            @click.outside="$wire.dispatch('user-not-mentioned.' + '{{ $editorId }}')"
            class="absolute bottom-[12rem] left-0 z-10 w-full"
        >
            <livewire:comments-user-list :guestModeEnabled="$model->guestModeEnabled()" :$editorId />
        </div>
    </div>
    <div class="min-h-6">
        @if ($errors->has('text'))
            <span class="align-top text-xs text-red-500 sm:text-sm">
                {{ __($errors->first('text')) }}
            </span>
        @endif
    </div>

    <div
        x-show="!showMsg"
        x-transition
        @comment-updated.window="(e) => {
                let key = @js($comment->getKey());
                if(e.detail.commentId === key && $wire.approvalRequired) {
                    showMsg = true;
                }
            }"
        class="space-x-4"
    >
        <x-comments::button wire:click="save" size="sm" dirtyTarget="text" loadingTarget="save">
            Save
        </x-comments::button>
        <x-comments::button wire:click="discard" size="sm" severity="info" type="button" loadingTarget="discard">
            Discard
        </x-comments::button>
    </div>

    <div x-show="showMsg" x-transition>
        <span x-transition class="align-top text-xs text-green-500 sm:text-sm">
            {{ __('Comment updated and will be displayed once approved !') }}
        </span>
    </div>
</div>

@script
    <script>
        let editorConfig = @js(config('comments.editor_config'));
        const quill = new Quill(`#${$wire.editorId}`, editorConfig);

        const editorElm = document.querySelector(`#${$wire.editorId} .ql-editor`);
        const toolbarParentElm = document.querySelector(`#${$wire.toolbarId}`);

        const toolbars = Array.from(document.querySelector('.ql-toolbar'));

        toolbarParentElm.append(toolbars.slice(-1));

        editorElm.innerHTML = $wire.text;

        quill.on('text-change', (delta, oldDelta, source) => {
            let html = editorElm.innerHTML;
            if (html === '<p><br></p>') {
                $wire.text = '';
                return;
            }
            $wire.text = html;
        });

        $wire.on('comment-update-discarded', function () {
            editorElm.innerHTML = @js($comment->text);
        });

        quill.on('text-change', () => handleEditorTextChange(editorElm, $wire));
        Livewire.on('user-selected.' + $wire.editorId, () => window.onMentionedUserSelected(event, quill, editorElm));
    </script>
@endscript
