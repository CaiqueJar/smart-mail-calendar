<?php

namespace App\Services;

use App\Models\GoogleAccount;
use Illuminate\Support\Facades\Http;

class GoogleAuthService
{
    public static function refreshToken(GoogleAccount $googleAccount): ?string
    {
        if (!$googleAccount->refresh_token) {
            return null;
        }

        $response = Http::asForm()->post(
            "https://oauth2.googleapis.com/token",
            [
                "client_id" => config("services.google.client_id"),
                "client_secret" => config("services.google.client_secret"),
                "refresh_token" => $googleAccount->refresh_token,
                "grant_type" => "refresh_token",
            ],
        );

        $data = $response->json();

        if (isset($data["error"])) {
            return null;
        }

        $googleAccount->update([
            "token" => $data["access_token"],
            "expires_in" => now()->addSeconds($data["expires_in"]),
        ]);

        return $data["access_token"];
    }
}
