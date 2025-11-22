<?php

namespace App\Console\Commands\Worker;

use App\Models\Request\Request;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class RemoveOldRequestRecords extends Command
{
    protected $signature = 'worker:remove-request-record';

    protected $description = 'Удаление старых записей о запросах';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->remove();

        return self::SUCCESS;
    }

    private function remove():void
    {
        $days = 15;
        $date = CarbonImmutable::now()->subDays($days);
        $count = 0;
        Request::query()
            ->where('created_at', '<', $date)
            ->get()
            ->each(function(Model $m) use (&$count){
                $m->forceDelete();
                $count++;
            });

        logger_info("Remove [$count] request records");
    }
}


