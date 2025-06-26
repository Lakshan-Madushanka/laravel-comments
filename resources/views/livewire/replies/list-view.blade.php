@php use LakM\Commenter\Enums\Sort;use LakM\Commenter\Helpers; @endphp
<div x-data="{ total: $wire.entangle('total') }" class="space-y-6">
    @if ($total > 1 && config('commenter.show_filters') && $showFilters)
        <div class="flex flex-col gap-y-2 sm:flex-row sm:items-center sm:justify-between !-mb-2">
            <div class="flex gap-x-2 overflow-auto overflow-x-auto sm:gap-x-3">
                <x-commenter::chip
                    wire:click="setSortBy('{{Sort::LATEST->value}}')"
                    wire:loading.class="!pointer-events-none"
                    :active="$sortBy === Sort::LATEST"
                >
                    {{ __('Newest') }}
                </x-commenter::chip>
                <x-commenter::chip
                    wire:click="setSortBy('{{Sort::OLDEST->value}}')"
                    wire:loading.class="!pointer-events-none"
                    :active="$sortBy === Sort::OLDEST"
                >
                    {{ __('Oldest') }}
                </x-commenter::chip>
                <x-commenter::chip
                    wire:click="setFilter('own')"
                    wire:loading.class="!pointer-events-none"
                    :active="$filter === 'own'"
                >
                    {{ __('My Replies') }}
                </x-commenter::chip>
            </div>
        </div>
    @endif

    <div wire:loading.flex wire.target="setSortBy" class="items-center gap-x-2 sm:gap-x-4">
        <x-commenter::spin class="!size-5" />
    </div>

    @if ($replies->isNotEmpty())
        @foreach ($replies as $reply)
            <livewire:replies.item-view
                :key="'reply-item' . $reply->id"
                :$message
                :$relatedModel
                :$reply
                :$guestMode
            />
        @endforeach
    @endif

    @if ($replies->isEmpty() && $filter === 'own')
        <div>{{ __('You haven\'t made/approved any replies yet !') }}</div>
    @endif

    @if ($replies->isNotEmpty() && config('commenter.reply.pagination.enabled') && $paginationRequired)
        <div class="flex items-center justify-center">
            @if ($limit < $currentTotal)
                <x-commenter::button wire:click="paginate" size="sm" type="button" loadingTarget="paginate">
                    {{ __('Load More') }}
                </x-commenter::button>
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

        $wire.on('unauthorized-reply-updated', (event) => {
            if (event.messageId === @js($message->getKey())) {
                $wire.$set('total', --$wire.total);
            }
        });

        Livewire.on("reply-created-@js($message->getKey())", () => {
            highlight();
        });

        $wire.on('more-replies-loaded', () => {
            highlight();
        });
    </script>
    @endscript
</div>
