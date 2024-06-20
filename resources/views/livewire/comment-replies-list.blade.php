<div x-data="{ total: $wire.entangle('total') }" class="space-y-6">
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
