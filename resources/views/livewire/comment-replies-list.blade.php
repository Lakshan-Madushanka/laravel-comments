<div
    @comment-created.window="$wire.$refresh"
    @comment-deleted.window="$wire.$refresh"
    class="space-y-8"
>
    @if ($replies->isNotEmpty())
        @foreach ($replies as $reply)
            <div
                x-ref="comment{{ $reply->getKey() }}"
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
                    @comment-update-discarded.window="(e) => {
                             if(e.detail.commentId === @js($reply->getKey())) {
                                   showUpdateForm = false;
                             }
                        }"
                    class="basis-full"
                >
                    <div x-show="!showUpdateForm" x-transition class="rounded border">
                        <div class="mb-2 flex items-center justify-between space-x-4 border-b bg-gray-100 p-1">
                            <div class="space-x-4">
                                <span class="font-bold">
                                    {{ $guestMode ? $reply->guest_name : $reply->commenter->name }}
                                </span>
                                <span
                                    x-text="moment(@js($reply->created_at)).format('YYYY/M/D H:mm')"
                                    class="text-xs"
                                ></span>
                            </div>

{{--                            <div class="flex justify-center items-center space-x-4">--}}
{{--                                @if ($model->canEditComment($reply))--}}
{{--                                    <div @click="showUpdateForm = !showUpdateForm">--}}
{{--                                        <x-comments::action class="text-sm">Edit</x-comments::action>--}}
{{--                                    </div>--}}
{{--                                @endif--}}
{{--                                    @if ($model->canDeleteComment($reply))--}}
{{--                                        <div wire:click="delete({{$reply}})" class="flex items-center">--}}
{{--                                            <x-comments::action wire:loading.remove wire:target="delete({{$reply}})" class="text-sm">Delete</x-comments::action>--}}
{{--                                            <x-comments::spin wire:loading wire:target="delete({{$reply}})" class="text-blue-500"/>--}}
{{--                                        </div>--}}
{{--                                    @endif--}}
{{--                            </div>--}}
                        </div>
                        <div
                            x-ref="text"
                            @comment-updated.window="(e) => {
                                let key = @js($reply->getKey());
                                if(e.detail.commentId === key) {
                                    if(@js($relatedModel->approvalRequired())) {
                                        let elm = 'comment'+ key;
                                         setTimeout(() => {
                                           $refs[elm].remove();
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

{{--                    <div wire:ignore x-show="!showUpdateForm" class="mt-2">--}}
{{--                        <livewire:comments-reactions-manager--}}
{{--                                :key="$reply->getKey()"--}}
{{--                                :$reply--}}
{{--                                :$guestMode--}}
{{--                                :$relatedModel--}}
{{--                        />--}}
{{--                    </div>--}}

{{--                    <div x-show="showUpdateForm" x-transition class="basis-full">--}}
{{--                        @if ($model->canEditComment($reply))--}}
{{--                            <livewire:comments-update-form--}}
{{--                                :comment="$reply"--}}
{{--                                :model="$model"--}}
{{--                                :key="$reply->getKey()"--}}
{{--                            />--}}
{{--                        @endif--}}
{{--                    </div>--}}
                </div>
            </div>
        @endforeach
    @else
{{--        <div class="text-lg">{{ __('Be the first one to make the comment !') }}</div>--}}
    @endif

    @if ($replies->isNotEmpty())
        <div class="flex items-center justify-center">
            @if ($limit < $total)
                <x-comments::button wire:click="paginate" size="sm" type="button" loadingTarget="paginate">
                    {{ __('Load More') }}
                </x-comments::button>
            @else
                <div class="font-bold">{{ __('End of comments') }}</div>
            @endif
        </div>
    @endif


    @script
    <script>
        const highlightSyntax = () => {
            document.querySelectorAll('.ql-code-block').forEach((el) => {
                el.removeAttribute('data-highlighted')
                window.hljs.highlightElement(el);
            }, {once: true});
        }

        highlightSyntax();

        $wire.on('comment-updated', () => {
            setTimeout(() => {
                highlightSyntax();

            }, 500)
        });
    </script>
    @endscript
</div>
