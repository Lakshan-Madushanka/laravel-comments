<?php

namespace LakM\Comments;

use Illuminate\Support\Facades\RateLimiter;

final class GuestModeRateLimiter
{
    public static $decaySeconds = 60;

    public static $maxAttempts = 3;

    public static function limiter(string $email)
    {
        return RateLimiter::attempt(
            key: 'guest-mode-verify-link:' . $email,
            maxAttempts: self::$maxAttempts,
            callback: function () {
                // Send message...
            },
            decaySeconds: self::$decaySeconds
        );
    }
}
