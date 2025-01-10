<?php

use App\Models\User;
use LakM\Comments\Enums\Sort;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Guest;
use LakM\Comments\Models\Reaction;
use LakM\Comments\Policies\CommentPolicy;
use LakM\Comments\Policies\ReplyPolicy;

return [
    /**
     * Available themes default,github
     */
    'theme' => 'modern',

    'primary_color' => '#0400ff',

    'secondary_color' => '#000000',

    'bg_primary_color' => '#ededed',

    'bg_secondary_color' => 'white',

    'active_color' => 'rgb(209,213,219)',

    'hover_color' => 'rgb(229,231,235)',

    'button_color' => '#0000FF',

    'button_hover_color' => '#0707a5',

    /**
     * Comment Model
     * Must extend base model LakM\Comments\Models\Comment
     */
    'model' => Comment::class,

    /**
     * Comment owner model
     * Must extend Illuminate\Contracts\Auth\Authenticatable
     * Must implement LakM\Comments\Contracts\CommenterContract
     */
    'user_model' => User::class,

    /**
     * Comment owner model in guest mode
     * Must extend LakM\Comments\Models\Guest
     */
    'guest_model' => Guest::class,

    /**
     * Database column or model accessor name to
     * get the name of the user.
     */
    'user_name_column' => 'name',

    /**
     * Database column or model accessor email to
     * get the email of the user.
     */
    'user_email_column' => 'email',

    /**
     * When name is null, it will be replaced with following value
     */
    'replace_null_name' => '?',

    /**
     * Reaction model
     * Must extend LakM\Comments\Models\Reaction
     */
    'reaction_model' => Reaction::class,

    // When guest mode unable no authentication required
    'guest_mode' => [
        'enabled' => true,
        'email_enabled' => true,
        'secured' => false,
    ],

    'auth_guard' => 'default',

    /**
     * In Auth mode unauthenticated users will be redirected to this route for login
     */
    'login_route' => 'login',

    /**
     * When set to true only approved commands will be displayed.
     * To approve a comment, approved column must be set to true in comments table.
     */
    'approval_required' => false,

    'profile_photo' => [
        /**
         * Database column or model accessor name to
         * get the url of profile photo.
         * Leave null if profile photo is not supported
         */
        'url_column' => '',
        'default' => [
            /**
             * when profile photo url haven't been set
             * this url is used.
             */
            'url' => '',
            /**
             * if this is empty
             *  gravatar service (https://docs.gravatar.com/api/avatars/images/) is used
             * to generate an avatar.
             */
            'gravatar' => [
                'default' => 'mp',
            ],
        ],
    ],

    /**
     * In Auth mode clicking profile photo will be redirected to this url
     * Keep null to disable this feature.
     * This can be table column name or accessor
     */
    'profile_url_column' => 'name',

    // When set to false filters won't be displayed in comments list
    'show_filters' => true,

    // Default comments sort order, See Sort::class for available values
    'default_sort' => Sort::TOP,

    // Comments pagination
    'pagination' => [
        'enabled' => true,
        'per_page' => 15,
    ],

    'permissions' => [
        'create-comment' => [CommentPolicy::class, 'create'],
        'update-comment' => [CommentPolicy::class, 'update'],
        'delete-comment' => [CommentPolicy::class, 'delete'],
        'create-reply' => [ReplyPolicy::class, 'create'],
        'update-reply' => [ReplyPolicy::class, 'update'],
        'delete-reply' => [ReplyPolicy::class, 'delete'],
    ],

    /**
     * Limit no of comments a user make for a model
     * keep null means unlimited
     */
    'limit' => null,

    // Control comment's replies
    'reply' => [
        'enabled' => true,
        /** when enabled email field is required to reply in guest mode */
        'email_enabled' => true,
        /** Keep null to allow unlimited replies for a comment */
        'limit' => null,
        /**
         * When set to true only approved replies will be displayed.
         * To approve a comment, approved column must be set to true in comments table.
         */
        'approval_required' => false,
        'pagination' => [
            'enabled' => true,
            'per_page' => 10,
        ],
        // Default comments sort order, available values: Sort::LATEST, Sort::OLDEST
        'default_sort' => Sort::LATEST,
    ],

    /**
     * Quill editor configs
     * @see https://quilljs.com/docs/configuration
     */
    'editor_config' => [
        'debug' => false,
        'modules' => [
            'toolbar' => [
                [['header' => [1, 2, false]]],
                ['bold', 'italic', 'underline'],
                ['link', 'code-block'],
            ],
        ],
        'placeholder' => 'Your message here',
        'theme' => 'snow',
    ],

    'should_confirm_link_visit' => true,

    'reactions' => [
        'like' => ['position' => 'left', 'fill' => 'gray'],
        'dislike' => ['position' => 'left', 'fill' => 'gray'],
        'happy' => ['position' => 'right', 'fill' => 'orange'],
        'love' => ['position' => 'right', 'fill' => 'red'],
        'sad' => ['position' => 'right', 'fill' => 'orange'],
    ],

    /**
     * available options
     * 'diff' (hour ago), 'standard' (2024/5/2 17:48)
     */
    'date_format' => 'diff',
];
