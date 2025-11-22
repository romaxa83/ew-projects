<?php

namespace App\Console\Commands\Commercial;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RemoveCommercialProjectUnitsCommand extends Command
{
    protected $signature = 'commercial:remove-units';

    protected $description = 'Удаление excel-файлов по юнитам коммерческого проекта';

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
        Storage::deleteDirectory('exports/commercial-project');
        logger_info("Remove excel-units");
    }

}

