<?php

namespace LakM\Comments\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LakM\Comments\Actions\VerifyGuestAction;

class VerifyGuestController
{
    public function __invoke(VerifyGuestAction $verifyGuestAction, Request $request): RedirectResponse
    {
        $verifyGuestAction->execute($request);

        $request->session()->flash('guest-email-verified', 'Your email verified successfully!');

        return redirect()->to($request->query('redirect_url') . '?verified=1');
    }
}
