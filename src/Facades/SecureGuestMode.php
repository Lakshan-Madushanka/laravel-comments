<?php

namespace LakM\Comments\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \LakM\Comments\SecureGuestModeManager
 */
class SecureGuestMode extends Facade
{
    public static function getFacadeAccessor()
    {
        return \LakM\Comments\SecureGuestModeManager::class;
    }
}
