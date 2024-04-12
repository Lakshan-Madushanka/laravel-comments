<?php

use App\Models\User;
use LakM\Comments\CommentPolicy;
use LakM\Comments\Models\Comment;

return [
    // Commentable Model
    'model' => Comment::class,

    // When guest mode unable no authentication required
    'guest_mode' => [
        'enabled' => true,
        'email_enabled' => true,
    ],

    'auth_guard' => 'default',

    'login_route' => 'login',

    'approval_required' => false,

    /**
     * Database column or model accessor name to
     * get the url of profile photo.
     * Leave null if profile photo is not supported
     */
    'profile_photo_url_column' => 'profile_photo_url',

    'pagination' => [
        'enabled' => true,
        'per_page' => 15,
    ],

    'permissions' => [
        'create-comment' => [CommentPolicy::class, 'create'],
        'update-comment' => [CommentPolicy::class, 'update']
    ],

    /**
     * Limit no of comments a user make for a model
     * keep null means unlimited
     */
    'limit' => null,

    /**
     * Quill editor configs
     * @see https://quilljs.com/docs/configuration
     */
    'editor_config' => [
        'debug' => 'info',
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

];
