<?php

namespace App\Console\Commands\Workers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteOldExcelFile extends Command
{
    protected $signature = 'worker:old_excel_file';

    public function handle()
    {
        try {
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

            $this->info("Delete [$count] file");
            logger_info("[worker] Remove old excel file [$count]");


            return self::SUCCESS;
        } catch (\Exception $e){
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}


