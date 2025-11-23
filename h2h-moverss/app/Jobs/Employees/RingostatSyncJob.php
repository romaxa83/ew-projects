<?php

namespace App\Jobs\Employees;

use App\Services\Employees\SyncEmployeeWithRingostat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RingostatSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $service = resolve(SyncEmployeeWithRingostat::class);
        $service->exec();
    }
}
