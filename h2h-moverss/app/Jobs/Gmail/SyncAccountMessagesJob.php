<?php

namespace App\Jobs\Gmail;

use App\Http\Controllers\Mailbox\Gmail\GMailController;
use App\Models\Mailbox\Gmail\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAccountMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public array $backoff = [300, 1800, 3600]; // 5m, 30m, 1h

    protected mixed $payload;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \JsonException
     */
    public function handle(GMailController $controller)
    {
        $accounts = Account::query()
            ->whereId($this->payload['ids'][0])
            ->get();
        try {
            $controller->fetchNewMsg('cron', $accounts);
        } catch (\JsonException $e) {
            report($e);
        }
    }
}
