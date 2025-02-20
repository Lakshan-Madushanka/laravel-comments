<div x-data="{ showMsg: false }">
    @if ($showEditor)
        <div>
            <livewire:comments-editor wire:model="text" :$editorId :$guestModeEnabled />
        </div>

        <div class="min-h-6">
            @if ($errors->has('text'))
                <span class="align-top text-xs text-red-500 sm:text-sm">
                    {{ __($errors->first('text')) }}
                </span>
            @endif

            <div x-show="showMsg" x-transition>
                <span x-transition class="align-top text-xs text-green-500 sm:text-sm">
                    {{ __('Reply updated and will be displayed once approved !') }}
                </span>
            </div>
        </div>

        <div
            x-show="!showMsg"
            x-transition
            @reply-updated.window="(e) => {
                let key = @js($reply->getKey());
                if(e.detail.replyId === key && $wire.approvalRequired) {
                    showMsg = true;
                }
            }"
        >
            <x-comments::button wire:click="save" size="sm" dirtyTarget="text" loadingTarget="save" class="me-4">
                {{ __('Save') }}
            </x-comments::button>
            <x-comments::button wire:click="discard" size="sm" severity="info" type="button" loadingTarget="discard">
                {{ __('Discard') }}
            </x-comments::button>
        </div>
    @endif
</div>
