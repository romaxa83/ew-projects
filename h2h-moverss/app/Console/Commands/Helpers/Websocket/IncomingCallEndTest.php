<?php

namespace App\Console\Commands\Helpers\Websocket;

use App\Enums\ProviderEnum;
use App\Events\Communications\IncomingCallAnswerOrEnd;
use App\Models\Calls\IncomingCall;
use App\Models\Client;
use App\Models\Zadarma\CallsEvents;
use Illuminate\Console\Command;

class IncomingCallEndTest extends Command
{
    protected $signature = 'websocket:incoming-end';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            echo PHP_EOL;
            $this->info("Done [time = {$time}]");
            echo PHP_EOL;

            return self::SUCCESS;
        } catch (\Throwable $e) {
            dd($e);
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    public function exec()
    {
        $callRec = CallsEvents::query()
            ->where('event', CallsEvents::EVENT_NOTIFY_OUT_START)
            ->latest()
            ->first();

        $client = Client::query()
            ->whereHas('phones', function ($query) use ($callRec) {
                $query->where('value', "LIKE", $callRec->destination);
            })
            ->first();

        $call = new IncomingCall();
        $call->provider = ProviderEnum::Zadarma();
        $call->call_id = $callRec->pbx_call_id;
        $call->phone = $callRec->destination;
        $call->client_id = $client->id ?? null;
        $call->save();

        broadcast(new IncomingCallAnswerOrEnd($call));

        $call->delete();
    }
}
