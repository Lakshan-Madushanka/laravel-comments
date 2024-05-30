@props(['fill' => 'none'])

<svg
    xmlns="http://www.w3.org/2000/svg"
    width="16"
    height="16"
    viewBox="0 0 24 24"
    fill="{{ $fill }}"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    {{ $attributes->merge(['class' => '']) }}
>
    <circle cx="12" cy="12" r="10" />
    <path d="M18 13a6 6 0 0 1-6 5 6 6 0 0 1-6-5h12Z" />
    <line x1="9" x2="9.01" y1="9" y2="9" />
    <line x1="15" x2="15.01" y1="9" y2="9" />
</svg>
