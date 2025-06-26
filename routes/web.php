<?php

use Illuminate\Support\Facades\Route;
use LakM\Commenter\Http\Controllers\VerifyGuestController;

Route::middleware(['web'])->get('/verify-guest', VerifyGuestController::class)->name('verify-guest');
