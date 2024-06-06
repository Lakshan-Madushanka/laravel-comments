@php
    use Illuminate\Support\Str;
@endphp

<div x-data="{ total: $wire.entangle('total') }" @reply-deleted.window="$wire.$refresh" class="space-y-6">
    @if ($replies->isNotEmpty())
        @foreach ($replies as $reply)
            <div
                x-ref="reply{{ $reply->getKey() }}"
                wire:key="{{ $reply->getKey() }}"
                class="flex gap-x-2 sm:gap-x-4"
            >
                <div class="basis-14">
                    <a href="{{ $reply->ownerPhotoUrl($authMode) }}" target="_blank">
                        <img
                            class="h-12 w-12 rounded-full border border-gray-200"
                            src="{{ $reply->ownerPhotoUrl($authMode) }}"
                            alt="{{ $reply->ownerName($authMode) }}"
                        />
                    </a>
                </div>
                <div
                    wire:ignore
                    x-data="{ showUpdateForm: false }"
                    @reply-update-discarded.window="(e) => {
                             if(e.detail.replyId === @js($reply->getKey())) {
                                   showUpdateForm = false;
                             }
                        }"
                    class="basis-full"
                >
                    <div x-show="!showUpdateForm" x-transition class="rounded border border-gray-200">
                        <div
                            class="mb-2 flex flex-col items-start justify-between space-x-4 border-b border-gray-200 bg-gray-100 p-1 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="space-x-1">
                                <span class="font-bold sm:hidden">
                                    {{ Str::limit($guestMode ? $reply->guest_name : $reply->commenter->name, 10) }}
                                </span>

                                <span class="hidden font-bold sm:inline">
                                    {{ Str::limit($guestMode ? $reply->guest_name : $reply->commenter->name, 25) }}
                                </span>

                                <span class="inline-block h-2 w-[1px] bg-black"></span>

                                @if (config('comments.date_format') === 'diff')
                                    <span class="text-xs">{{ $reply->created_at->diffForHumans() }}</span>
                                @else
                                    <span
                                        x-text="moment(@js($reply->created_at)).format('YYYY/M/D H:mm')"
                                        class="text-xs"
                                    ></span>
                                @endif

                                @if ($reply->isEdited())
                                    <span class="inline-block h-2 w-[1px] bg-black"></span>
                                    <span class="text-xs">Edited</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-center space-x-4">
                                @if ($this->canUpdateReply($reply))
                                    <div @click="showUpdateForm = !showUpdateForm">
                                        <x-comments::action class="text-sm">Edit</x-comments::action>
                                    </div>
                                @endif

                                @if ($this->canDeleteReply($reply))
                                    <div
                                        wire:click="delete({{ $reply }})"
                                        wire:confirm="Are you sure you want to delete this reply?"
                                        class="flex items-center"
                                    >
                                        <x-comments::action
                                            wire:loading.remove
                                            wire:target="delete({{$reply}})"
                                            class="text-sm"
                                        >
                                            Delete
                                        </x-comments::action>
                                        <x-comments::spin
                                            wire:loading
                                            wire:target="delete({{$reply}})"
                                            class="!text-blue-500"
                                        />
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div
                            x-ref="text"
                            @reply-updated.window="(e) => {
                                let key = @js($reply->getKey());
                                if(e.detail.replyId === key) {
                                    if(e.detail.approvalRequired) {
                                        let elm = 'reply'+ key;
                                         setTimeout(() => {
                                           $refs[elm].remove();
                                           total -= 1;
                                         }, 2000);
                                        return;
                                    }
                                    $refs.text.innerHTML = e.detail.text;
                                    showUpdateForm = false;
                                }
                            }"
                            class="p-1"
                        >
                            {!! $reply->text !!}
                        </div>
                    </div>

                    <div wire:ignore x-show="!showUpdateForm" class="mt-2">
                        <livewire:comments-reactions-manager
                            :key="$reply->getKey()"
                            :comment="$reply"
                            :$guestMode
                            :$relatedModel
                            :enableReply="false"
                        />
                    </div>

                    <div x-show="showUpdateForm" x-transition class="basis-full">
                        @if ($this->canUpdateReply($reply))
                            <livewire:comments-reply-update-form
                                :key="$reply->getKey()"
                                :$reply
                                :guestModeEnabled="$guestMode"
                            />
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    @if ($replies->isNotEmpty() && config('comments.reply.pagination.enabled'))
        <div class="flex items-center justify-center">
            @if ($limit < $total)
                <x-comments::button wire:click="paginate" size="sm" type="button" loadingTarget="paginate">
                    {{ __('Load More') }}
                </x-comments::button>
            @else
                <div class="font-bold">{{ __('End of replies') }}</div>
            @endif
        </div>
    @endif

    @script
        <script>
            setTimeout(() => {
                highlightSyntax();
            }, 1500);

            $wire.on('reply-updated', () => {
                setTimeout(() => {
                    highlightSyntax();
                }, 1500);
            });

            Livewire.on('reply-created', () => {
                setTimeout(() => {
                    highlightSyntax();
                }, 1500);
            });

            $wire.on('more-comments-loaded', () => {
                setTimeout(() => {
                    highlightSyntax();
                }, 1500);
            });
        </script>
    @endscript
</div>
