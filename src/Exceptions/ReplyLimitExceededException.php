<?php

namespace LakM\Comments\Exceptions;

use Exception;

final class ReplyLimitExceededException extends Exception
{
    public static function make(?int $limit): static
    {
        $msg = sprintf('Allowed reply limit %d exceeded', $limit);
        return new self(message: $msg);
    }
}
