<?php

namespace App\Console\Commands\Helpers;

use App\Models\GPS\Alert;
use App\Models\GPS\History;
use Illuminate\Console\Command;

class SetDeviceToAlert extends Command
{
    protected $signature = 'helper:set_device_to_alert';

    protected $count = 0;

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

            logger_info("[helper] ".__CLASS__." [time = {$time}], [req = $this->count]");
            $this->info("[helper] ".__CLASS__." [time = {$time}], [req = $this->count]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            logger_info('[helper] '.__CLASS__, [
                'msg' => $e->getMessage()
            ]);
            return self::FAILURE;
        }
    }

    private function exec(): void
    {
        Alert::query()
            ->whereNull('device_id')
            ->get()
            ->each(function (Alert $item) {
                $history = History::query()
                    ->select(['device_id'])
                    ->where('id', $item->history_id)
                    ->first();

                $item->device_id = $history->device_id;
                $item->save();
                $this->count++;
            })
        ;
    }
}




