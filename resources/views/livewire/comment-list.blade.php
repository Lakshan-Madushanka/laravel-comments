<div
    x-data="{
        total: $wire.entangle('total'),
        getTotal: function () {
            return '(' + this.total + ')'
        },
    }"
    @unauthorized-comment-updated.window="$wire.$refresh"
    class="space-y-6"
>
    <div class="text-lg font-bold">
        {{ __('Comments') }}
        <span x-text="getTotal()"></span>
    </div>
    <div class="flex flex-col gap-y-2 overflow-auto sm:flex-row sm:items-center sm:justify-between">
        @if (($comments->count() > 1 || $sortBy !== 'my_comments') && config('comments.show_filters'))
            <div class="flex gap-x-2 sm:gap-x-3">
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
            <livewire:comments-item :key="'comment'. $comment->id" :$comment :$guestMode :$model :$showReplyList />
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
            const highlight = () => {
                setTimeout(() => {
                    highlightSyntax();
                }, 1500);
            }

            highlight();

            $wire.on('filter-applied', () => {
                highlight()
            });

            $wire.on('comment-updated', () => {
               highlight()
            });

            Livewire.on('comment-created', () => {
                highlight()
            });

            $wire.on('more-comments-loaded', () => {
                highlight()
            });
        </script>
    @endscript
</div>
