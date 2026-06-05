<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Jobs\ProcessMails;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;

class GmailController extends Controller
{
    public function checkMails()
    {
        $user = Auth::user();
        $token = $user->googleAccount->getValidToken();

        if (!$token) {
            return redirect()->route('auth.google')->withErrors(['msg' => 'Failed to refresh Google token. Please login again.']);
        }

        $response = Http::withToken($token)->get(
            "https://gmail.googleapis.com/gmail/v1/users/me/messages",
            [
                "q" => "classroom",
            ],
        );

        if (!$response->ok()) {
            throw new \Exception($response->body());
        }

        $messages = $response->json();
        if (!isset($messages["messages"]) || empty($messages["messages"])) {
            throw new \Exception("No messages were found!");
        }

        $messages = $messages["messages"];

        $messages = array_filter($messages, function ($message) {
            return !Message::where("source_id", $message["id"])->exists();
        });

        ProcessMails::dispatch($messages, $user->id, $user->googleAccount);

        return response()->json([
            "status" => "success",
            "message" => count($messages) . " new messages are being processed.",
        ]);
    }
}
