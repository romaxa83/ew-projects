<?php

namespace App\Console\Commands\Worker;

use App\Models\Orders\Dealer\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RemoveDealerOrderPdf extends Command
{
    protected $signature = 'worker:remove-dealer-order';

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
//        Storage::deleteDirectory(Order::PDF_FILE_GENERATE_DIR);
        logger_info("Remove " . Order::PDF_FILE_GENERATE_DIR);
    }
}
