<?php

namespace LakM\Comments\Exceptions;

use Exception;

class ReplyLimitExceeded extends Exception
{
    public static function make(?int $limit): static
    {
        $msg = sprintf('Allowed reply limit %d exceeded', $limit);
        return new static(message: $msg);
    }
}
