@props([
    'height' => 24,
    'width' => 24,
    'fill' => 'none',
    'strokeColor' => 'currentColor',
])

<svg
    xmlns="http://www.w3.org/2000/svg"
    width="{{ $height }}"
    height="{{ $width }}"
    viewBox="0 0 24 24"
    fill="{{ $fill }}"
    stroke="{{ $strokeColor }}"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    class="lucide lucide-user-round-check"
>
    <path d="M2 21a8 8 0 0 1 13.292-6" />
    <circle cx="10" cy="8" r="5" />
    <path d="m16 19 2 2 4-4" />
</svg>
