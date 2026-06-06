<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[
    Fillable([
        "user_id",
        "source_id",
        "title",
        "sender",
        "sent_at",
        "priority",
        "should_create_calendar_event",
        "category",
        "content",
    ]),
]
class Message extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
