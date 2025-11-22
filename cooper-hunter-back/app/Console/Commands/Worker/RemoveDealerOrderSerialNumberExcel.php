<?php

namespace App\Console\Commands\Worker;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RemoveDealerOrderSerialNumberExcel extends Command
{
    protected $signature = 'worker:remove-dealer-order-excel';

    protected $description = "Удаляет старые pdf файлы";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->removeTmpPdf();

        return self::SUCCESS;
    }

    private function removeTmpPdf():void
    {
        Storage::deleteDirectory('exports/dealer-order');
        logger_info("Remove excel-dealer-order-serial-numbers");
    }
}
