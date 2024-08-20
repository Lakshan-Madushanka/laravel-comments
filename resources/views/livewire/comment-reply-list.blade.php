@php use LakM\Comments\Enums\Sort;use LakM\Comments\Helpers; @endphp
<div x-data="{ total: $wire.entangle('total') }" class="space-y-6">
    <div class="flex flex-col gap-y-2 sm:flex-row sm:items-center sm:justify-between">
        @if (($replies->count() > 1 || $sortBy !== 'my_comments') && config('comments.show_filters'))
            <div class="flex gap-x-2 overflow-auto overflow-x-auto sm:gap-x-3">
                <x-comments::chip
                    wire:click="setSortBy('latest')"
                    wire:loading.class="!pointer-events-none"
                    @class([
                        'bg-gray-200' => $sortBy === Sort::LATEST && Helpers::isDefaultTheme(),
                        'bg-gray-500' => $sortBy === Sort::LATEST && Helpers::isGithubTheme(),
                    ])
                >
                    {{ __('Newest') }}
                </x-comments::chip>
                <x-comments::chip
                    wire:click="setSortBy('oldest')"
                    wire:loading.class="!pointer-events-none"
                    @class([
                       'bg-gray-200' => $sortBy === Sort::OLDEST && Helpers::isDefaultTheme(),
                        'bg-gray-500' => $sortBy === Sort::OLDEST && Helpers::isGithubTheme(),
                    ])
                >
                    {{ __('Oldest') }}
                </x-comments::chip>
                <x-comments::chip
                    wire:click="setFilter('my_replies')"
                    wire:loading.class="!pointer-events-none"
                    @class([
                        'bg-gray-200' => $filter === 'my_replies' && Helpers::isDefaultTheme(),
                        'bg-gray-500' => $filter === 'my_replies' && Helpers::isGithubTheme(),
                    ]) >
                    {{ __('My Replies') }}
                </x-comments::chip>
            </div>
        @endif
    </div>

    <div wire:loading.flex wire.target="setSortBy" class="flex items-center gap-x-2 sm:gap-x-4">
        <x-comments::spin class="!size-5 !text-blue-500" />
    </div>

    @if ($replies->isNotEmpty())
        @foreach ($replies as $reply)
            <livewire:comments-reply-item
                :key="'reply-item' . $reply->id"
                :$comment
                :$relatedModel
                :$reply
                :$guestMode
            />
        @endforeach
    @endif

    @if ($replies->isEmpty() && $filter === 'my_replies')
        <div>{{ __('You haven\'t made/approved any replies yet !') }}</div>
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

        Livewire.on(`reply-created-${@js($comment->getKey())}`, () => {
            highlight();
        });

        $wire.on('more-replies-loaded', () => {
            highlight();
        });
    </script>
    @endscript
</div>
