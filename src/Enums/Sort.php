<?php

namespace LakM\Commenter\Enums;

enum Sort: string
{
    case TOP = 'top';
    case LATEST = 'latest';
    case OLDEST = 'oldest';
    case REPLIES = 'replies';
}
