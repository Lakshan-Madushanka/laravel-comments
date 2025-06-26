<?php

namespace LakM\Commenter\Facades;

use Illuminate\Support\Facades\Facade;
use LakM\Commenter\SecureGuestModeManager;

/**
 * @mixin SecureGuestModeManager
 */
class SecureGuestMode extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return SecureGuestModeManager::class;
    }
}
