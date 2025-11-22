<?php

namespace App\Console\Commands\Import;

use App\Models\Catalogs\Car\Brand;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class ImportBrand extends Command
{
    protected $signature = 'import:brand';

    protected $description = 'Import car brand to csv file';

    public function handle()
    {
        $models = Brand::all();
        $path = __DIR__ . "/../../../../database/seeders/_brands.csv";
        $file = fopen($path, 'w');

        $this->info("Import brands to [{$path}]");
        $progressBar = new ProgressBar($this->output, count($models));
        $progressBar->setFormat('verbose');
        $progressBar->start();

        foreach ($models as $item){
            /** @var $item Brand */
            fputcsv($file, [
                $item->uuid,
                $item->is_main,
                $item->active,
                $item->sort,
                $item->name,
                $item->color,
                $item->hourly_payment,
                $item->discount_hourly_payment,
            ]);

            $progressBar->advance();
        }

        fclose($file);

        $progressBar->finish();
        echo PHP_EOL;
    }
}
