@php
    use Illuminate\Support\Str;
@endphp

<div
    x-data="{
        total: $wire.entangle('total'),
        getTotal: function () {
            return '(' + this.total + ')'
        },
    }"
    class="space-y-6"
>
    <div class="text-lg font-bold">
        {{ __('Comments') }}
        <span x-text="getTotal()"></span>
    </div>
    <div class="flex flex-col gap-y-2 sm:flex-row sm:items-center sm:justify-between">
        @if (($comments->count() > 1 || $sortBy !== 'my_comments') && config('comments.show_filters'))
            <div class="flex gap-x-2 overflow-auto sm:gap-x-3">
                <div class="w-14"></div>
                <x-comments::chip
                    wire:click="setSortBy('top')"
                    wire:loading.class="!pointer-events-none"
                    @class([
                        'hover:bg-gray-500 cursor-pointer transition ml-[-6px] sm:ml-[2px] text-nowrap',
                        '!bg-gray-500' => $sortBy === 'top',
                    ])
                >
                    {{ __('Top') }}
                </x-comments::chip>
                <x-comments::chip
                    wire:click="setSortBy('latest')"
                    wire:loading.class="!pointer-events-none"
                    @class(['hover:bg-gray-500 cursor-pointer text-nowrap transition', '!bg-gray-500' => $sortBy === 'latest'])
                >
                    {{ __('Newest') }}
                </x-comments::chip>
                <x-comments::chip
                    wire:click="setSortBy('oldest')"
                    wire:loading.class="!pointer-events-none"
                    @class(['hover:bg-gray-500 cursor-pointer text-nowrap transition', '!bg-gray-500' => $sortBy === 'oldest'])
                >
                    {{ __('Oldest') }}
                </x-comments::chip>
                <x-comments::chip
                    wire:click="setSortBy('replies')"
                    wire:loading.class="!pointer-events-none"
                    @class(['hover:bg-gray-500 cursor-pointer text-nowrap transition', '!bg-gray-500' => $sortBy === 'replies'])
                >
                    {{ __('Replies') }}
                </x-comments::chip>

                <x-comments::chip
                    wire:click="setFilter('my_comments')"
                    wire:loading.class="!pointer-events-none"
                    @class(['hover:bg-gray-500 cursor-pointer text-nowrap transition', '!bg-gray-500' => $filter === 'my_comments'])
                >
                    {{ __('My Comments') }}
                </x-comments::chip>
            </div>
        @endif

        <x-comments::link type="a" route="#create-comment-form">{{ __('Create Comment') }}</x-comments::link>
    </div>

    <div wire:loading.flex class="flex items-center gap-x-2 sm:gap-x-4">
        <div class="basis-14"></div>
        <x-comments::spin class="!size-5 !text-blue-500" />
    </div>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $comment)
            <div
                x-ref="comment{{ $comment->getKey() }}"
                wire:key="comment-{{ $loop->index }}"
                class="flex gap-x-2 sm:gap-x-4"
            >
                <div class="basis-14">
                    <a href="{{ $profileUrl ?? $comment->ownerPhotoUrl($authMode) }}" target="_blank">
                        <img
                            class="h-12 w-12 rounded-full border border-gray-200"
                            src="{{ $comment->ownerPhotoUrl($authMode) }}"
                            alt="{{ $comment->ownerName($authMode) }}"
                        />
                    </a>
                </div>
                <div
                    x-data="{ showUpdateForm: false }"
                    @comment-update-discarded.window="(e) => {
                                 if(e.detail.commentId === @js($comment->getKey())) {
                                       showUpdateForm = false;
                                 }
                            }"
                    class="basis-full"
                >
                    <div x-show="!showUpdateForm" x-transition class="rounded border border-gray-200">
                        <div
                            class="mb-2 flex flex-col items-start border-b border-gray-100 bg-gray-100 p-1 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="space-x-1">
                                <span class="font-bold sm:hidden">
                                    {{ Str::limit($guestMode ? $comment->guest_name : $comment->commenter->name, 10) }}
                                </span>

                                <span class="hidden font-bold sm:inline">
                                    {{ Str::limit($guestMode ? $comment->guest_name : $comment->commenter->name, 25) }}
                                </span>

                                <span class="inline-block h-2 w-[1px] bg-black"></span>

                                @if (config('comments.date_format') === 'diff')
                                    <span class="text-xs">{{ $comment->created_at->diffForHumans() }}</span>
                                @else
                                    <span
                                        x-text="moment(@js($comment->created_at)).format('YYYY/M/D H:mm')"
                                        class="text-xs"
                                    ></span>
                                @endif

                                @if ($comment->isEdited())
                                    <span class="inline-block h-2 w-[1px] bg-black"></span>
                                    <span class="text-xs">{{ __('Edited') }}</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-center space-x-2 sm:space-x-4">
                                @if ($model->canEditComment($comment))
                                    <div @click="showUpdateForm = !showUpdateForm" class="flex items-center">
                                        <x-comments::action class="text-xs sm:text-sm">
                                            {{ __('Edit') }}
                                        </x-comments::action>
                                    </div>
                                @endif

                                @if ($model->canDeleteComment($comment))
                                    <div
                                        wire:click="delete({{ $comment }})"
                                        wire:confirm="{{ __('Are you sure you want to delete this comment?') }}"
                                        class="flex items-center"
                                    >
                                        <x-comments::action
                                            wire:loading.remove
                                            wire:target="delete({{$comment}})"
                                            class="align-text-bottom text-xs sm:text-sm"
                                        >
                                            {{ __('Delete') }}
                                        </x-comments::action>
                                        <x-comments::spin
                                            wire:loading
                                            wire:target="delete({{$comment}})"
                                            class="!text-blue-500"
                                        />
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div
                            x-ref="text"
                            @comment-updated.window="(e) => {
                                        let key = @js($comment->getKey());
                                        if(e.detail.commentId === key) {
                                            if(@js($model->approvalRequired())) {
                                                let elm = 'comment'+ key;
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
                            {!! $comment->text !!}
                        </div>
                    </div>

                    <!--Reaction manager -->
                    <div x-show="!showUpdateForm" class="mt-2">
                        <livewire:comments-reactions-manager
                            :key="'reaction-manager-' . $loop->index"
                            :$comment
                            :relatedModel="$model"
                        />
                    </div>

                    <!-- Update Form -->
                    @if ($model->canEditComment($comment))
                        <div x-show="showUpdateForm" x-transition class="basis-full">
                            <livewire:comments-update-form
                                :key="'update-form-'. $loop->index"
                                :comment="$comment"
                                :model="$model"
                            />
                        </div>
                    @endif

                    @if (config('comments.reply.enabled'))
                        <div
                            x-data="{ showReplyList: @js($showReplyList), replyCount: @js($comment->replies_count) }"
                            @reply-created.window="
                                    if($event.detail.commentId === {{ $comment->getKey() }}) {
                                        replyCount += 1;
                                    }
                                  "
                            @reply-deleted.window="
                                    if($event.detail.commentId === {{ $comment->getKey() }}) {
                                        replyCount -= 1;
                                    }
                                    "
                            class="mt-2"
                        >
                            <div
                                x-show="replyCount > 0"
                                x-transition
                                @click="$dispatch('show-replies.' + @js($comment->getKey())); showReplyList = !showReplyList"
                                class="inline-block"
                            >
                                <x-comments::link
                                    type="popup"
                                    class="inline-flex items-center border-b-0 px-2 py-1 transition hover:rounded hover:border-b-0 hover:bg-gray-200 [&>*]:pr-1"
                                >
                                    <x-comments::icons.chevron-down x-show="!showReplyList" />
                                    <x-comments::icons.chevron-up x-show="showReplyList" />
                                    <span x-text="replyCount"></span>
                                    <span>{{ __('replies') }}</span>
                                </x-comments::link>
                            </div>

                            <!-- Reply List -->
                            <div x-show="showReplyList" x-transtion class="ml-[-2rem] mt-6 sm:ml-8">
                                <livewire:comments-reply-list
                                    :key="'reply-list-'. $loop->index"
                                    :$comment
                                    :relatedModel="$model"
                                    :total="$comment->replies_count"
                                />
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="text-lg">{{ __('Be the first one to make a comment !') }}</div>
    @endif

    @if ($comments->isNotEmpty() && $model->paginationEnabled() && $paginationRequired)
        <div class="flex items-center justify-center">
            @if ($limit < $total)
                <x-comments::button wire:click="paginate" type="button" loadingTarget="paginate">
                    {{ __('Load More') }}
                </x-comments::button>
            @else
                <div class="font-bold">{{ __('End of comments') }}</div>
            @endif
        </div>
    @endif

    @script
        <script>
            highlightSyntax();

            $wire.on('filter-applied', () => {
                setTimeout(() => {
                    highlightSyntax();
                }, 1500);
            });

            $wire.on('comment-updated', () => {
                setTimeout(() => {
                    highlightSyntax();
                }, 1500);
            });

            Livewire.on('comment-created', () => {
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
