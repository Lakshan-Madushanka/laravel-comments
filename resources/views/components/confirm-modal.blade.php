@props(['header' => null, 'body' => null])


<div x-cloak x-show="modalIsOpen" x-transition.opacity.duration.200ms x-trap.inert.noscroll="modalIsOpen"
     x-on:keydown.esc.window="modalIsOpen = false" x-on:click.self="modalIsOpen = false"
     class="fixed inset-0 z-30 flex items-end justify-center p-4 pb-8 bg-gray-800/75 backdrop-blur-md sm:items-center lg:p-8"
     role="dialog" aria-modal="true" aria-labelledby="defaultModalTitle">
    <!-- Modal Dialog -->
    <div x-show="modalIsOpen"
         x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
         x-transition:enter-start="opacity-0 scale-50" x-transition:enter-end="opacity-100 scale-100"
         class="flex max-w-lg shadow-lg text-white flex-col rounded-lg gap-4 overflow-hidden rounded-radius border border-outline bg-surface bg-gray-900 text-on-surface no-dark:border-outline-dark no-dark:bg-surface-dark-alt no-dark:text-on-surface-dark">

        <!-- Dialog Header -->
        @if ($header)
            <div
                class="flex items-center justify-between border-b  border-outline bg-slate-800 p-4 no-dark:border-outline-dark no-dark:bg-surface-dark/20">
                <h3 id="defaultModalTitle"
                    class="font-semibold tracking-wide text-on-surface-strong no-dark:text-on-surface-dark-strong">
                    {{$header}}
                </h3>
                <button x-on:click="modalIsOpen = false" aria-label="close modal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor"
                         fill="none" stroke-width="1.4" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        <!-- Dialog Body -->
        <div class="px-4 py-8">
            <p>
                {{$body}}
            </p>
        </div>
        <!-- Dialog Footer -->
        <div
            class="flex flex-col-reverse justify-between gap-2 border-t border-outline bg-slate-800 p-4 no-dark:border-outline-dark no-dark:bg-surface-dark/20 sm:flex-row sm:items-center md:justify-end">
            <button x-on:click="$dispatch('action-cancelled'); modalIsOpen = false" type="button"
                    class="whitespace-nowrap rounded-radius px-4 py-2 text-center text-sm font-medium tracking-wide text-on-surface transition hover:opacity-75 focus-visible:outline  focus-visible:outline-offset-2 focus-visible:outline-primary active:opacity-100 active:outline-offset-0 no-dark:text-on-surface-dark no-dark:focus-visible:outline-primary-dark">
                Cancel
            </button>
            <button x-on:click="$dispatch('action-confirmed');modalIsOpen = false" type="button"
                    class="whitespace-nowrap rounded-radius bg-primary border border-primary no-dark:border-primary-dark px-4 py-2 text-center text-sm font-medium tracking-wide text-on-primary transition hover:opacity-75 focus-visible:outline  focus-visible:outline-offset-2 focus-visible:outline-primary active:opacity-100 active:outline-offset-0 no-dark:bg-primary-dark no-dark:text-on-primary-dark no-dark:focus-visible:outline-primary-dark">
                Confirm
            </button>
        </div>
    </div>
</div>
