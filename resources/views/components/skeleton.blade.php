<div class="space-y-6">
    <div class="text-lg font-bold">
        {{ __('Comments') }}
    </div>
    @for($i=0; $i<5; $i++)
        <div class="flex animate-pulse ">
            <div class="shrink-0">
                <span class="size-12 block bg-gray-200 rounded-full dark:bg-neutral-700"></span>
            </div>

            <div class="ms-4 mt-2 w-full">
                <p class="h-4 bg-gray-200 rounded-full dark:bg-neutral-700" style="width: 40%;"></p>

                <ul class="mt-5 space-y-3">
                    <li class="w-full h-4 bg-gray-200 rounded-full dark:bg-neutral-700"></li>
                    <li class="w-full h-4 bg-gray-200 rounded-full dark:bg-neutral-700"></li>
                    <li class="w-full h-4 bg-gray-200 rounded-full dark:bg-neutral-700"></li>
                    <li class="w-full h-4 bg-gray-200 rounded-full dark:bg-neutral-700"></li>
                </ul>
            </div>
        </div>
    @endfor

    <div class="flex items-center justify-center w-full h-48 bg-gray-200 rounded dark:bg-gray-700 animate-pulse">
    </div>
</div>
