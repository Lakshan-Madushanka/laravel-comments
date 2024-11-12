@php use LakM\Comments\Enums\Sort;use LakM\Comments\Facades\SecureGuestMode;use LakM\Comments\Helpers; @endphp
<div
    x-data="{
        total: $wire.entangle('total'),
        getTotal: function () {
            return '(' + this.total + ')'
        },
    }"
    @unauthorized-comment-updated.window="$wire.$refresh"
    class="lakm_commenter space-y-6"
>
    <div class="text-lg font-bold">
        {{ __('Comments') }}
        <span x-text="getTotal()"></span>
    </div>
    <div class="flex flex-col gap-y-2 sm:flex-row sm:items-center sm:justify-between">
        @if (($total > 1 || $filter === 'own') && config('comments.show_filters'))
            <div class="flex gap-x-2 sm:gap-x-3 overflow-auto">
                <div class="w-14"></div>
                <x-comments::chip
                    wire:click="setSortBy('{{Sort::TOP->value}}')"
                    wire:loading.class="!pointer-events-none"
                    :active="$sortBy === Sort::TOP"
                >
                    {{ __('Top') }}
                </x-comments::chip>
                <x-comments::chip
                    wire:click="setSortBy('{{Sort::LATEST->value}}')"
                    wire:loading.class="!pointer-events-none"
                    :active="$sortBy === Sort::LATEST"
                >
                    {{ __('Newest') }}
                </x-comments::chip>
                <x-comments::chip
                    wire:click="setSortBy('{{Sort::OLDEST->value}}')"
                    wire:loading.class="!pointer-events-none"
                    :active="$sortBy === Sort::OLDEST"
                >
                    {{ __('Oldest') }}
                </x-comments::chip>
                <x-comments::chip
                    wire:click="setSortBy('{{Sort::REPLIES->value}}')"
                    wire:loading.class="!pointer-events-none"
                    :active="$sortBy === Sort::REPLIES"
                >
                    {{ __('Replies') }}
                </x-comments::chip>

                <x-comments::chip
                    wire:click="setFilter('own')"
                    wire:loading.class="!pointer-events-none"
                    :active="$filter === 'own'"
                >
                    {{ __('My Comments') }}
                </x-comments::chip>
            </div>
        @endif

        <div class="flex gap-x-2">
            <div>
                <x-comments::link type="a" route="#create-comment-form">{{ __('Create Comment') }}</x-comments::link>
            </div>
            @if($guestMode && SecureGuestMode::enabled() && SecureGuestMode::allowed())
                <div class="w-1 h-6 bg-slate-500"></div>
                <x-comments::link type="button" wire:click="logOut">{{ __('Log out') }}</x-comments::link>
            @endif
        </div>
    </div>

    <div wire:loading.flex class="flex items-center gap-x-2 sm:gap-x-4">
        <div class="basis-14"></div>
        <x-comments::spin class="!size-5 !text-blue-500" />
    </div>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $comment)
            <livewire:comments-item :key="'comment'. $comment->id" :$comment :$guestMode :$model :$showReplyList />
        @endforeach
    @elseif ($filter === 'own')
        <div class="text-lg">{{ __('You haven\'t made/approved any comments yet !') }}</div>
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
        };

        highlight();

        $wire.on('filter-applied', () => {
            highlight();
        });

        $wire.on('comment-updated', () => {
            highlight();
        });

        Livewire.on('comment-created', () => {
            highlight();
        });

        $wire.on('more-comments-loaded', () => {
            highlight();
        });
    </script>
    @endscript
</div>
