<?php

namespace LakM\Comments\Exceptions;

final class InvalidEditorIDException extends \Exception
{
    public static function make(): static
    {
        return new self(message: 'Editor id must be valid UUID');
    }
}
