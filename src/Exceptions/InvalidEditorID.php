<?php

namespace LakM\Comments\Exceptions;

class InvalidEditorID extends \Exception
{
    public static function make(): static
    {
        return new static(message: 'Editor id must be valid UUID');
    }
}
