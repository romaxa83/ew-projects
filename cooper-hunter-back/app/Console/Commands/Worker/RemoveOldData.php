<?php

namespace App\Console\Commands\Worker;

use App\Console\Commands\Auth\RemoveExpiredSmsTokensCommand;
use App\Console\Commands\Auth\RemoveExpiredTokensCommand;
use App\Console\Commands\Commercial\RemoveCommercialProjectUnitsCommand;
use App\Console\Commands\Commercial\RemoveQuoteHistoryPdfFileCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RemoveOldData extends Command
{
    protected $signature = 'worker:remove-old-data';

    protected $description = "Запускает все воркеры, по удалению устаревших данных";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        Artisan::call(RemoveExpiredTokensCommand::class);
        Artisan::call(RemoveExpiredSmsTokensCommand::class);
        Artisan::call(RemoveQuoteHistoryPdfFileCommand::class);
        Artisan::call(RemoveCommercialProjectUnitsCommand::class);
        Artisan::call(RemoveDealerOrderPdf::class);
        Artisan::call(RemoveDealerOrderSerialNumberExcel::class);
        Artisan::call(RemoveOldRequestRecords::class);

        return self::SUCCESS;
    }
}
