<?php

use LakM\Comments\CommentPolicy;
use LakM\Comments\Models\Comment;

return [
    // Commentable Model
    'model' => Comment::class,

    // When guest mode unable no authentication required
    'guest_mode' => [
        'enabled' => false,
        'email_enabled' => true,
    ],

    'auth_guard' => 'default',

    'login_route' => 'login',

    'permissions' => [
        'create-comment' => [CommentPolicy::class, 'create'],
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
