@php use Illuminate\Support\Str; @endphp

@props(['modelClass', 'modelId'])

<div x-data class="space-y-8">
    <livewire:comments-list modelClass="{{$modelClass}}" modelId="{{$modelId}}"/>
    <hr/>
    <livewire:comments-form modelClass="{{$modelClass}}" modelId="{{$modelId}}"/>
</div>
