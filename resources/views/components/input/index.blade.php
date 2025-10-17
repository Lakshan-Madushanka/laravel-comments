@props([
    'type' => 'text',
    'shouldDisable' => false,
])

<input
    @disabled($shouldDisable)
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'py-2 px-4 block w-full border-2 border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none no-dark:bg-slate-900 no-dark:border-gray-700 no-dark:text-white no-dark:focus:ring-gray-600']) }}
/>
