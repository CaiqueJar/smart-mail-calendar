<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;

class GoogleAuthController extends Controller
{
    public function auth()
    {
        return Socialite::driver("google")
            ->scopes([
                "https://www.googleapis.com/auth/gmail.readonly",
                "https://www.googleapis.com/auth/calendar.events",
            ])
            ->with([
                "access_type" => "offline",
                "prompt" => "consent",
            ])
            ->redirect();
    }

    public function callback()
    {
        try {
            $userSocialite = Socialite::driver("google")->stateless()->user();
        } catch (\Exception $e) {
            return redirect("/auth/google")->withErrors(["auth" => "Falha ao autenticar com Google"]);
        }

        $user = User::updateOrCreate(
            ["email" => $userSocialite->email],
            [
                "name" => $userSocialite->name,
                "email" => $userSocialite->email,
                "password" => Hash::make(Str::random()),
            ],
        );

        $user->googleAccount()->updateOrCreate(
            ["google_id" => $userSocialite->id],
            [
                "google_id" => $userSocialite->id,
                "token" => $userSocialite->token,
                "refresh_token" => $userSocialite->refreshToken,
                "expires_in" => now()->addSeconds($userSocialite->expiresIn),
            ],
        );

        Auth::login($user, true);

        return redirect()->route('gmail.checkmails');
    }
}
