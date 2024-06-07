<?php

use App\Models\User;
use LakM\Comments\Models\Comment;
use LakM\Comments\Models\Reaction;
use LakM\Comments\Policies\CommentPolicy;
use LakM\Comments\Policies\ReplyPolicy;

return [
    /**
     * Commentable Model
     * Must extends base model LakM\Comments\Models\Comment
     */
    'model' => Comment::class,

    /**
     * Comment owner model
     * Must extends Illuminate\Contracts\Auth\Authenticatable
     */
    'user_model' => User::class,

    /**
     * Reaction model
     * Must extends LakM\Comments\Models\Reaction
     */
    'reaction_model' => Reaction::class,

    // When guest mode unable no authentication required
    'guest_mode' => [
        'enabled' => true,
        'email_enabled' => true,
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

    // When set to false filters won't be displayed in comments list
    'show_filters' => true,

    // Comments pagination
    'pagination' => [
        'enabled' => true,
        'per_page' => 35,
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
            'per_page' => 15,
        ],
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
                ['code-block'],
            ],
        ],
        'placeholder' => 'write your comment',
        'theme' => 'snow',
    ],

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

    'admin_panel' => [
        'enabled' => true,
        'routes' => [
            'middlewares' => [],
            'prefix' => 'admin',
        ],
    ],
];
