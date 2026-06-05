<?php

namespace App\Jobs;

use App\Models\GoogleAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Jobs\ProcessSingleMail;

class ProcessMails implements ShouldQueue
{
    use Queueable;

    private array $messages;
    private int $userId;
    private GoogleAccount $googleToken;

    /**
     * Create a new job instance.
     */
    public function __construct(array $messages, int $userId, GoogleAccount $googleToken)
    {
        $this->messages = $messages;
        $this->userId = $userId;
        $this->googleToken = $googleToken;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->messages as $message) {
            ProcessSingleMail::dispatch($message, $this->userId, $this->googleToken);
        }
    }
}
