<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\GoogleAuthService;

#[Fillable(["user_id", "google_id", "token", "refresh_token", "expires_in"])]
#[Hidden(["token", "refresh_token"])]
class GoogleAccount extends Model
{
    protected $casts = [
        "expires_in" => "datetime",
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getValidToken(): ?string
    {
        if ($this->expires_in->isFuture()) {
            return $this->token;
        }

        return GoogleAuthService::refreshToken($this);
    }
}
