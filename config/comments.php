<?php

use LakM\Comments\CommentPolicy;
use LakM\Comments\Models\Comment;

return [
    // Commentable Model
    'model' => Comment::class,

    // When guest mode unable no authentication required
    'quest_mode' => false,

    'auth_guard' => 'default',

    'login_route' => 'login',

    'permissions' => [
        'create-comment' => [CommentPolicy::class, 'create'],
    ],

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
