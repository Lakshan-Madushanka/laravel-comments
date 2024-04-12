@props(['modelClass', 'modelId'])

<div x-cloak x-data class="space-y-8">
    <livewire:comments-list modelClass="{{$modelClass}}" modelId="{{$modelId}}"/>
    <hr/>
    <livewire:comments-create-form modelClass="{{$modelClass}}" modelId="{{$modelId}}"/>
</div>
