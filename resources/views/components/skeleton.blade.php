@props([
    'noOfComments' => config('commenter.skeleton.no_of_comments',
    5),
])

<div class="space-y-6">
    <div class="text-lg font-bold">
        {{ __('Comments') }}
    </div>
    @for ($i=0; $i<$noOfComments; $i++)
        <div class="flex animate-pulse">
            <div class="shrink-0">
                <span class="block size-12 rounded-full bg-gray-200 dark:bg-neutral-700"></span>
            </div>

            <div class="ms-4 mt-2 w-full">
                <p class="h-4 rounded-full bg-gray-200 dark:bg-neutral-700" style="width: 40%"></p>

                <ul class="mt-5 space-y-3">
                    <li class="h-4 w-full rounded-full bg-gray-200 dark:bg-neutral-700"></li>
                    <li class="h-4 w-full rounded-full bg-gray-200 dark:bg-neutral-700"></li>
                    <li class="h-4 w-full rounded-full bg-gray-200 dark:bg-neutral-700"></li>
                    <li class="h-4 w-full rounded-full bg-gray-200 dark:bg-neutral-700"></li>
                </ul>
            </div>
        </div>
    @endfor

    <div class="flex h-48 w-full animate-pulse items-center justify-center rounded bg-gray-200 dark:bg-gray-700"></div>
</div>
