<?php

namespace App\Jobs\API;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Http, Log;

class ZaiperNewLeadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected mixed $payload;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 50; // ~2 days

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public array $backoff = [300, 1800, 3600]; // 5m, 30m, 1h

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
     * @param  Client  $client
     * @return void
     * @throws RequestException
     */
    public function handle(Client $client): void
    {
        $payload = $this->payload;
        $payload['contact'] = $client->with(['phones', 'emails'])
            ->find($this->payload['client_id'], ['id', 'name', 'lname'])
            ->toArray();

        $response = Http::post('https://hooks.zapier.com/hooks/catch/16481687/3rrkqlb/', $payload);

        $response->throwIf($response['status'] !== 'success');

        Log::info('Zapier WebHook response', (array) $response->object());
    }
}
