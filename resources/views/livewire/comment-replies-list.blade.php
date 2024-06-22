<div x-data="{ total: $wire.entangle('total') }" class="space-y-6">
    <div class="flex flex-col gap-y-2 sm:flex-row sm:items-center sm:justify-between">
        @if (($replies->count() > 1 || $sortBy !== 'my_comments') && config('comments.show_filters'))
            <div class="flex gap-x-2 overflow-auto overflow-x-auto sm:gap-x-3">
                <x-comments::chip
                    wire:click="setSortBy('latest')"
                    wire:loading.class="!pointer-events-none"
                    @class(['hover:bg-gray-500 cursor-pointer !px-[4px] !py-[1px] text-nowrap transition', '!bg-gray-500' => $sortBy === 'latest'])
                >
                    {{ __('Newest') }}
                </x-comments::chip>
                <x-comments::chip
                    wire:click="setSortBy('oldest')"
                    wire:loading.class="!pointer-events-none"
                    @class(['hover:bg-gray-500 cursor-pointer !px-[4px] !py-[1px] text-nowrap transition', '!bg-gray-500' => $sortBy === 'oldest'])
                >
                    {{ __('Oldest') }}
                </x-comments::chip>
                <x-comments::chip
                    wire:click="setFilter('my_comments')"
                    wire:loading.class="!pointer-events-none"
                    @class(['hover:bg-gray-500 cursor-pointer !px-[4px] !py-[1px] text-nowrap transition', '!bg-gray-500' => $filter === 'my_comments'])
                >
                    {{ __('My Comments') }}
                </x-comments::chip>
            </div>
        @endif
    </div>

    <div wire:loading.flex wire.target="setSortBy" class="flex items-center gap-x-2 sm:gap-x-4">
        <x-comments::spin class="!size-5 !text-blue-500" />
    </div>

    @if ($replies->isNotEmpty())
        @foreach ($replies as $reply)
            <livewire:comments-reply-item :key="'reply' . $reply->id" :$comment :$relatedModel :$reply :$guestMode />
        @endforeach
    @endif

    @if ($replies->isNotEmpty() && config('comments.reply.pagination.enabled') && $paginationRequired)
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
            const highlight = () => {
                setTimeout(() => {
                    highlightSyntax();
                }, 1500);
            };

            highlight();

            $wire.on('show-reply', () => {
                highlight();
            });

            $wire.on('filter-applied', () => {
                highlight();
            });

            $wire.on('reply-updated', () => {
                highlight();
            });

            Livewire.on('reply-created', () => {
                highlight();
            });

            $wire.on('more-replies-loaded', () => {
                highlight();
            });
        </script>
    @endscript
</div>
