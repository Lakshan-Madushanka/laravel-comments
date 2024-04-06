<?php

namespace LakM\Comments\Exceptions;

use Exception;

class CommentLimitExceeded extends Exception
{
    public static function make(string $modelName, ?int $limit): static
    {
        $msg = sprintf('Allowed comment limit (%d) for the %s exceeded', $limit, $modelName);
        return new static(message: $msg);
    }
}
