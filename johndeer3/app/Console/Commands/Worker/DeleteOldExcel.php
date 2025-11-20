<?php

namespace App\Console\Commands\Worker;

use App\Services\Telegram\TelegramDev;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

class DeleteOldExcel extends Command
{
    protected $signature = 'jd:delete-excel';

    protected $description = 'Delete old excel';

    public function handle()
    {
        $storage = Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix();
        $filePath = "{$storage}/excel/";

        $count = 0;

        if(file_exists("{$storage}/excel/")){
            $data = glob("{$filePath}*");
            $count = count($data);

            foreach ($data as $file){
                unlink($file);
            }
        }

        \Log::notice("Old Excel Report - удалено ( {$count} ) старых файлов");
        TelegramDev::info("🗑 Old Excel Report - удалено [{$count}] старых файлов");

        $this->info('Done');
    }
}
