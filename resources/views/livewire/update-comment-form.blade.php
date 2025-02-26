<div x-data="{ showMsg: false }">
    <div>
        <livewire:comments-editor wire:model="text" :$editorId :guestModeEnabled="$model->guestModeEnabled()" />
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
    >
        <x-comments::button wire:click="save" class="me-4" size="sm" dirtyTarget="text" loadingTarget="save">
            {{ __('Save') }}
        </x-comments::button>
        <x-comments::button wire:click="discard" size="sm" severity="info" type="button" loadingTarget="discard">
            {{ __('Discard') }}
        </x-comments::button>
    </div>

    <div x-show="showMsg" x-transition>
        <span x-transition class="align-top text-xs text-green-500 sm:text-sm">
            {{ __('Comment updated and will be displayed once approved !') }}
        </span>
    </div>
</div>
