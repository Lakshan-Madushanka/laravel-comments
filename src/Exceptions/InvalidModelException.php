<?php

namespace LakM\Comments\Exceptions;

use LakM\Comments\Concerns\Commentable;

final class InvalidModelException extends \Exception
{
    public static function make(string $message): static
    {
        return new self(message: $message);
    }
}
