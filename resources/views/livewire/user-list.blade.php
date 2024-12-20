<div>
    @if ($show)
        <div
            @class([
                "scrollbar max-h-96 min-h-96 w-full overflow-auto rounded bg-gray-200 shadow-lg dark:!text-white dark:!bg-black",
                "hover:!bg-["  . config('comments.hover_color') . "]",
            ])
            @style([
                'background: ' . config('comments.bg_primary_color'),
            ])
        >
            <div
                x-data="{
                    initFocus:false,
                    setFocusFirst: function() {
                      if(!this.initFocus) {
                        this.initFocus = true
                        $focus.wrap().first()
                      }
                    },
                     setFocusLast: function() {
                      if(!this.initFocus) {
                        this.initFocus = true
                        $focus.wrap().last()
                      }
                    }
                 }"
                @keydown.up="$focus.wrap().previous();"
                @keydown.down="$focus.wrap().next()"
                @keydown.up.window="setFocusFirst()"
                @keydown.down.window="setFocusLast()"
                class="space-y-1"
            >
                @foreach ($users as $user)
                    <button
                        wire:click="userSelected(@js($user->name))"
                        class="flex w-full cursor-pointer items-center gap-x-4 p-2 transition hover:bg-gray-300 focus:!border-0 focus:!bg-gray-300 focus:outline-none"
                        type="button"
                    >
                        <img src="{{ $user->photo }}" alt="" class="h-8 w-8 rounded-full" />
                        <span class="font-bold">
                            <span>@</span>
                            {{ $user->name }}
                        </span>
                    </button>
                @endforeach
            </div>

            @if ($this->limit < $this->total)
                <div x-intersect="$wire.loadMore" class="invisible"></div>
            @endif

            <div wire:loading.flex class="mt-4 flex justify-center">
                <x-comments::spin
                    class="size-6 text-center !text-blue-500"
                    @style([
                        'color: ' . config('comments.primary_color'),
                    ])
                />
            </div>
        </div>
    @endif
</div>
