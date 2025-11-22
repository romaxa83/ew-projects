<?php

namespace App\Console\Commands\Import;

use App\Models\Catalogs\Car\Model;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class ImportModel extends Command
{
    protected $signature = 'import:model';

    protected $description = 'Import car model to csv file';

    public function handle()
    {
        $models = Model::all();
        $path = __DIR__ . "/../../../../database/seeders/_models.csv";
        $file = fopen($path, 'w');

        $this->info("Import models to [{$path}]");
        $progressBar = new ProgressBar($this->output, count($models));
        $progressBar->setFormat('verbose');
        $progressBar->start();

        foreach ($models as $item){
            /** @var $item Model */
            fputcsv($file, [
                $item->uuid,
                $item->active,
                $item->sort,
                $item->name,
                $item->brand_id,
                $item->for_credit,
                $item->for_calc,
            ]);

            $progressBar->advance();
        }

        fclose($file);

        $progressBar->finish();
        echo PHP_EOL;
    }
}

