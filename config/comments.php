<?php

return [];
use App\Models\User;
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
