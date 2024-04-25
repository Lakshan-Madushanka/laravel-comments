@php
    use Illuminate\Support\Facades\Auth;
@endphp

<div>
    <div class="flex w-full justify-between">
        <div class="flex space-x-4 rounded border bg-gray-100 p-1">
            @foreach ($lReactions as $key => $value)
                @if ($key === "like")
                    <div
                        x-data="{
                            isLiked: $wire.reactions['like']['reacted'],
                            showUsers: false,
                        }"
                        @mouseleave="showUsers=false"
                        @comment-disliked.window="(e) => {
                            if(e.detail.id === @js($comment->getKey())) {
                                    isLiked = false;
                            }
                        }"
                        @click="
                            if(isLiked === true) {
                                $dispatch('comment-liked', {id: @js($comment->getKey())});
                            }
                        "
                    >
                        <div
                            @click="isLiked = !isLiked; showUsers=false"
                            wire:click="handle('{{ $key }}', '{{ $value["model"] }}')"
                            @if (! $authMode)
                                @mouseover="
                                    if($wire.reactions['{{ $key }}']['count'] > 0 && !showUsers) {
                                         showUsers = true;
                                         $wire.lastReactedUser('{{ $key }}')
                                     }
                                     "
                            @endif
                            class="flex cursor-pointer items-center"
                        >
                            <div x-show="!isLiked">
                                <x-dynamic-component component="comments::icons.{{$key}}" />
                            </div>
                            <div x-show="isLiked">
                                <x-dynamic-component
                                    component="comments::icons.{{$key}}"
                                    :fill="$this->fillColor('like')"
                                />
                            </div>
                            <div class="ml-2">
                                <span class="text-sm">{{ $reactions["like"]["count"] }}</span>
                            </div>
                        </div>

                        <x-comments::show-reacted-users
                            @mouseleave="showUsers=false"
                            :$lastReactedUserName
                            :$reactions
                            :$key
                            :$comment
                            :$authMode
                            class="bottom-[-3.4rem] left-[-2rem]"
                            wrapperClass="left-0 bottom-[-2rem]"
                        />
                    </div>
                @elseif ($key === "dislike")
                    <div
                        x-data="{
                            isDisliked: $wire.reactions['dislike']['reacted'],
                            showUsers: false,
                        }"
                        @mouseleave="showUsers=false"
                        @comment-liked.window="(e) => {
                            if(e.detail.id === @js($comment->getKey())) {
                                    isDisliked = false;
                            }
                        }"
                        @click="
                            if(isDisliked === true) {
                                $dispatch('comment-disliked', {id: @js($comment->getKey())});
                            }
                        "
                        class="cursor-pointer"
                    >
                        <div
                            @click="isDisliked = !isDisliked; showUsers=false"
                            wire:click="handle('{{ $key }}', '{{ $value["model"] }}')"
                            @if (!$authMode)
                                @mouseover="
                                 if($wire.reactions['{{ $key }}']['count'] > 0 && !showUsers) {
                                     showUsers = true;
                                     $wire.lastReactedUser('{{ $key }}')
                                 }
                                 "
                            @endif
                            class="flex cursor-pointer items-center"
                        >
                            <div x-show="!isDisliked">
                                <x-dynamic-component component="comments::icons.{{$key}}" />
                            </div>

                            <div x-show="isDisliked">
                                <x-dynamic-component
                                    component="comments::icons.{{$key}}"
                                    :fill="$this->fillColor('dislike')"
                                />
                            </div>

                            <div class="ml-2">
                                <span class="text-sm">{{ $reactions["dislike"]["count"] }}</span>
                            </div>
                        </div>
                        <x-comments::show-reacted-users
                            @mouseleave="showUsers=false"
                            :$lastReactedUserName
                            :$reactions
                            :$key
                            :$comment
                            :$authMode
                            class="bottom-[-3.4rem] left-[-2rem]"
                            wrapperClass="left-0 bottom-[-2rem]"
                        />
                    </div>
                @else
                    <x-comments::show-reaction :$comment :$lastReactedUserName :$reactions :$key :$authMode />
                @endif
            @endforeach
        </div>

        <div class="flex space-x-2 rounded border bg-gray-100">
            @foreach ($rReactions as $key => $value)
                <x-comments::show-reaction :$comment :$lastReactedUserName :$reactions :$key :$authMode/>
            @endforeach
        </div>
    </div>

    @if ($authMode)
        <x-comments::modal
            x-data="{show: false, type: ''}"
            @show-user-list.window="
            if($wire.get('id') == $event.detail.id) {
                 show=true;
                 type=$event.detail.type;
            }
        "
            loadingTarget="loadReactedUsers"
        >
            <div class="flex py-4">
                <div class="space-y-2 border-r-2">
                    <div class="mb-4 border-b-2 p-4">
                        <span class="bg-gray-300 px-4 py-2 font-bold">{{ $total }}</span>
                    </div>
                    @foreach (config("comments.reactions") as $key => $reaction)
                        <div
                            @if ($reactions[$key]["count"] > 0)
                                wire:click="loadReactedUsers('{{ $key }}')"
                                @click="type = '{{ $key }}'"
                                class="cursor-pointer p-4 relative"
                            @endif
                            wire:loading.class="cursor-not-allowed"
                            target="loadReactedUsers"
                            class="relative p-4"
                            :class="type === '{{ $key }}' ? 'bg-gray-100' : ''"
                        >
                            @if ($reactions[$key]["reacted"])
                                <x-dynamic-component
                                    component="comments::icons.{{$key}}"
                                    :fill="$this->fillColor($key)"
                                />
                            @else
                                <x-dynamic-component component="comments::icons.{{$key}}" />
                            @endif

                            <span class="absolute left-8 top-1 rounded bg-gray-300 px-1 text-xs">
                                {{ $reactions[$key]["count"] }}
                            </span>
                        </div>
                    @endforeach
                </div>

                @if ($selectedReactionType)
                    <div class="mt-4 flex w-full flex-col items-start px-4">
                        @foreach ($this->getReactedUsers($selectedReactionType) as $user)
                            <div class="flex w-full items-center space-x-4 border-b p-2">
                                <div>
                                    <img
                                        class="h-[1.8rem] w-[1.8rem] rounded-full border border-gray-200"
                                        src="{{ $user->photo }}"
                                        alt="{{ $user->photo }}"
                                    />
                                </div>
                                <div>{{ $user->name }}</div>
                            </div>
                        @endforeach

                        @if ($this->getReactedUsersLimit($selectedReactionType) < $reactions[$selectedReactionType]["count"])
                            <div class="!mt-4 flex w-full justify-center">
                                <x-comments::button
                                    wire:click="loadReactedUsers('{{$selectedReactionType}}')"
                                    type="button"
                                    size="sm"
                                >
                                    Load
                                </x-comments::button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </x-comments::modal>
    @endif
</div>
