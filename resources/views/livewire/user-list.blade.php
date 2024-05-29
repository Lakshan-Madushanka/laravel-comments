<div>
    @if($show)
        <div class='bg-gray-200 shadow-lg w-full min-h-96 max-h-96 rounded scrollbar overflow-auto'>
            <div
                @keydown.up="$focus.wrap().previous();"
                @keydown.down="$focus.wrap().next()"
                x-init=" $nextTick(() => { $focus.wrap().first() });"
                class="space-y-1"

            >
                @foreach($users as $user)
                    <button
                        wire:click="userSelected('{{$user->name}}')"
                        class="flex w-full items-center gap-x-4 hover:bg-gray-300 p-2 cursor-pointer transition focus:!bg-gray-300 focus:outline-none focus:!border-0"
                        type="button"
                    >
                        <img
                            src="{{$user->photo}}"
                            alt=""
                            class="w-8 h-8 rounded-full"
                        >
                        <span class="font-bold"><span>@</span>{{$user->name}}</span>
                    </button>
                @endforeach
            </div>

            @if($this->limit < $this->total)
                <div x-intersect="$wire.loadMore" class="invisible"></div>
            @endif

            <div wire:loading.flex class="flex justify-center mt-4">
                <x-comments::spin class="!text-blue-500 size-6 text-center"/>
            </div>
        </div>

    @endif
</div>
