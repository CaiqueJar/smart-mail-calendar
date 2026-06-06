<?php

use App\Http\Controllers\GmailController;
use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::get("/auth/google", [GoogleAuthController::class, "auth"])->name("login");
Route::get("/auth/google/callback", [GoogleAuthController::class, "callback"]);

Route::middleware("auth")
    ->get("/gmail/checkmail", [GmailController::class, "checkMails"])
    ->name("gmail.checkmails");