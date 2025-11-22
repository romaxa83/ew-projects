<?php

namespace App\Jobs;

use App\Imports\Spares\SparesImportManager;
use App\Models\Catalogs\Calc\SparesDownloadFile;
use App\Services\Telegram\TelegramDev;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportSpares implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private SparesDownloadFile $record,
    )
    {}

    public function handle(): void
    {
        try {
            $this->record->toggleStatusProcess();

            TelegramDev::info("Запущен процесс импорта запчастей по файлу {$this->record->file->pathToFileStorage()}", $this->record->type);

            (new SparesImportManager(
                $this->record->file->pathToFileStorage(),
                $this->record->type))->handle();

            $this->record->toggleStatusDone();
        } catch (\Throwable $e){
            $this->record->toggleStatusError($e->getMessage());
            \Log::error($e->getMessage());
            // @todo dev-telegram
            TelegramDev::error(__FILE__, $e, $this->record->type);
        }
    }
}
