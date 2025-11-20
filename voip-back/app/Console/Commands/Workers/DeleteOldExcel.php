<?php

namespace App\Console\Commands\Workers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

class DeleteOldExcel extends Command
{
    protected $signature = 'workers:remove_old_excel';

    protected $description = 'Удаляет старые excel файлы';

    public function handle()
    {
        try {
            $start = microtime(true);

            $storage = Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix();
            $filePath = "{$storage}exports/reports/";

            $count = 0;
            if(file_exists("{$storage}exports/reports/")){
                $data = glob("{$filePath}*");
                $count = count($data);

                foreach ($data as $file){
                    unlink($file);
                }
            }

            $time = microtime(true) - $start;
            logger_info("[worker] DELETE OLD EXCEL FILES [{$count}] [time = {$time}]");
        } catch (\Exception $e){
            logger_info("[worker] DELETE OLD EXCEL FILES FAIL", [$e]);
            $this->error($e->getMessage(), []);
        }
    }
}
