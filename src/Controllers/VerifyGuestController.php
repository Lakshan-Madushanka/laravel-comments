<?php

namespace LakM\Comments\Controllers;

use Illuminate\Http\Request;
use LakM\Comments\Actions\VerifyGuestAction;

class VerifyGuestController
{
    public function __invoke(VerifyGuestAction $verifyGuestAction, Request $request)
    {
        $verifyGuestAction->execute($request);

        $request->session()->flash('guest-email-verified', 'Your email verified successfully!');

        return redirect()->to($request->query('redirect_url') . '?verified=1');
    }
}
