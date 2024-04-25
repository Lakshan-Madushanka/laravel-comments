@php use Illuminate\Support\Facades\Auth;@endphp

<div>
    <div class="flex w-full justify-between">
        <div class="flex space-x-4 bg-gray-100 p-1 rounded border">
            @foreach($lReactions as $key => $value)
                @if($key === 'like')
                    <div
                        x-data="{isLiked: $wire.reactions['like']['reacted'], showUsers: false}"
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
                            wire:click="handle('{{$key}}', '{{$value['model']}}')"
                            @if(Auth::check())
                                @mouseover="
                                    if($wire.reactions['{{$key}}']['count'] > 0 && !showUsers) {
                                         showUsers = true;
                                         $wire.lastReactedUser('{{$key}}')
                                     }
                                     "
                            @endif
                            class="flex items-center cursor-pointer"
                        >
                            <div x-show="!isLiked">
                                <x-dynamic-component component="comments::icons.{{$key}}"/>
                            </div>
                            <div x-show="isLiked">
                                <x-dynamic-component
                                    component="comments::icons.{{$key}}"
                                    :fill="$this->fillColor('like')"
                                />
                            </div>
                            <div class="ml-2">
                                <span class="text-sm">{{$reactions['like']['count']}}</span>
                            </div>
                        </div>

                        <x-comments::show-reacted-users
                            @mouseleave="showUsers=false"
                            :lastReactedUserName="$lastReactedUserName"
                            :reactions="$reactions"
                            :key="$key"
                            :comment="$comment"
                            class="left-[-2rem] bottom-[-3.4rem]"
                            wrapperClass="left-0 bottom-[-2rem]"
                        />
                    </div>
                @elseif($key === 'dislike')
                    <div
                        x-data="{isDisliked: $wire.reactions['dislike']['reacted'], showUsers: false}"
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
                            wire:click="handle('{{$key}}', '{{$value['model']}}')"
                            @if(Auth::check())
                                @mouseover="
                                     if($wire.reactions['{{$key}}']['count'] > 0 && !showUsers) {
                                         showUsers = true;
                                         $wire.lastReactedUser('{{$key}}')
                                     }
                                     "
                            @endif
                            class="flex items-center cursor-pointer"
                        >
                            <div x-show="!isDisliked">
                                <x-dynamic-component component="comments::icons.{{$key}}"/>
                            </div>

                            <div x-show="isDisliked">
                                <x-dynamic-component
                                    component="comments::icons.{{$key}}"
                                    :fill="$this->fillColor('dislike')"
                                />
                            </div>

                            <div class="ml-2">
                                <span class="text-sm">{{$reactions['dislike']['count']}}</span>
                            </div>
                        </div>
                        <x-comments::show-reacted-users
                            @mouseleave="showUsers=false"
                            :$lastReactedUserName
                            :$reactions
                            :$key
                            :$comment
                            class="left-[-2rem] bottom-[-3.4rem]"
                            wrapperClass="left-0 bottom-[-2rem]"
                        />
                    </div>
                @else
                    <x-comments::show-reaction :$comment :$lastReactedUserName :$reactions :$key/>
                @endif
            @endforeach
        </div>

        <div class="flex space-x-2 rounded bg-gray-100 border">
            @foreach($rReactions as $key => $value)
                <x-comments::show-reaction :$comment :$lastReactedUserName :$reactions :$key/>
            @endforeach
        </div>
    </div>

    @if(Auth::check())
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
                <div class="border-r-2 space-y-2">
                    <div class="p-4 border-b-2 mb-4">
                        <span class="bg-gray-300 px-4 py-2 font-bold">{{$total}}</span>
                    </div>
                    @foreach(config('comments.reactions') as $key => $reaction)
                        <div
                            @if($reactions[$key]['count'] > 0)
                                wire:click="loadReactedUsers('{{$key}}')"
                            @click="type = '{{$key}}'"
                            class="cursor-pointer p-4 relative"
                            @endif
                            wire:loading.class="cursor-not-allowed"
                            target="loadReactedUsers"
                            class="p-4 relative"
                            :class="type === '{{$key}}' ? 'bg-gray-100' : ''"
                        >
                            @if($reactions[$key]['reacted'])
                                <x-dynamic-component component="comments::icons.{{$key}}"
                                                     :fill="$this->fillColor($key)"/>
                            @else
                                <x-dynamic-component component="comments::icons.{{$key}}"/>
                            @endif

                            <span
                                class="absolute text-xs top-1 left-8 rounded px-1 bg-gray-300">{{$reactions[$key]['count']}}</span>
                        </div>
                    @endforeach
                </div>

                @if($selectedReactionType)
                    <div class="w-full flex items-start px-4 mt-4 flex-col">
                        @foreach($this->getReactedUsers($selectedReactionType) as  $user)
                            <div class="flex w-full items-center border-b p-2 space-x-4">
                                <div>
                                    <img
                                        class="rounded-full border border-gray-200 w-[1.8rem] h-[1.8rem]"
                                        src="{{ $user->photo }}"
                                        alt="{{ $user->photo }}"
                                    />
                                </div>
                                <div>{{$user->name}}</div>
                            </div>
                        @endforeach

                        @if($this->getReactedUsersLimit($selectedReactionType) < $reactions[$selectedReactionType]['count'])
                            <div class="!mt-4 flex justify-center w-full">
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

