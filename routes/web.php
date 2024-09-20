<?php

use Illuminate\Support\Facades\Route;
use LakM\Comments\Http\Controllers\VerifyGuestController;

Route::middleware(['web'])->get('/verify-guest', VerifyGuestController::class)->name('verify-guest');
