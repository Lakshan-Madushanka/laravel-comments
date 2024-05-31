@props([
    'model',
])

<div x-cloak x-data class="space-y-8">
    <livewire:comments-list :model="$model" />
    <hr class="text-gray-400" />
    <livewire:comments-create-form :model="$model" />
</div>
