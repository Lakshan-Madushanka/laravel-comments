<?php

namespace LakM\Comments\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LakM\Comments\Helpers;
use LakM\Comments\Models\Guest;
use LakM\NoPass\Facades\NoPass;
use App\Models\User;

class VerifyGuestAction
{
    public function execute(Request $request): void
    {
        $guest = Guest::query()->where('email', $request->query('mail'))->first();

        $noPass = NoPass::for($guest)
            ->email();

        if ($noPass->isValid()) {
            Helpers::getAuthGuard()->login($guest);
            $request->session()->regenerate();
            $noPass->inValidate();
        }
    }
}
