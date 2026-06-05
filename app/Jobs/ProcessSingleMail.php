<?php

namespace App\Jobs;

use App\Actions\SendWhatsappMessage;
use App\Ai\Agents\MessageAgent;
use App\Models\GoogleAccount;
use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Enums\Lab;

class ProcessSingleMail implements ShouldQueue
{
    use Queueable;
    private array $message;
    private int $userId;
    private GoogleAccount $googleToken;

    public int $tries = 5;
    public int $backoff = 10;
    public int $maxExceptions = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(array $message, int $userId, GoogleAccount $googleToken)
    {
        $this->message = $message;
        $this->userId = $userId;
        $this->googleToken = $googleToken;
    }

    public function backoff(): array
    {
        return [10, 30, 60, 300];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = Http::withToken($this->googleToken->getValidToken())->get(
                "https://gmail.googleapis.com/gmail/v1/users/me/messages/{$this->message["id"]}",
                [
                    "q" => "classroom",
                ],
            );

            $mail = $response->json();
            $headers = collect($mail["payload"]["headers"]);
            $subject = $headers->firstWhere("name", "Subject")["value"] ?? null;

            $body = collect($mail["payload"]["parts"])->first();
            $bodyRaw = $body["body"]["data"] ?? "";
            $body = $this->base64UrlDecode($bodyRaw);
            $body = mb_convert_encoding($body, "UTF-8", "UTF-8");
            
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'rate limited')) {
                $this->release(delay: now()->addSeconds(60));
                return;
            }
            logger()->error("Erro ao processar email", ["message" => $e->getMessage()]);
        }
    }

    private function base64UrlDecode($data)
    {
        $data = str_replace(["-", "_"], ["+", "/"], $data);
        return base64_decode($data);
    }
}
