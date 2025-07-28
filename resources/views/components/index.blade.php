@props([
    'model',
])

<div class="lakm_commenter">
    <div
        x-cloak
        x-data
        class="space-y-8 dark:text-white"
    >
        @if(config('commenter.should_confirm_link_visit'))
            <x-commenter::link-visit-confirm-modal/>
        @endif
        <livewire:comments.list-view :lazy="config('commenter.lazy_loading', true)" :model="$model"/>
        <hr class="text-gray-400"/>
        <div id="create-comment-form">
            <livewire:comments.create-form :model="$model"/>
        </div>
    </div>
</div>

