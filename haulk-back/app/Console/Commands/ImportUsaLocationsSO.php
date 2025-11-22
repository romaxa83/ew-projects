<?php

namespace App\Console\Commands;

use App\Imports\UsaCitiesImportSO;
use App\Imports\UsaStatesImportSO;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Storage;

class ImportUsaLocationsSO extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:import-usa-so';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import USA locations SO database';

    /**
     * Execute the console command.
     *
     * https://www.serviceobjects.com/public/zipcode/ZipCodeFiles.zip
     *
     */
    public function handle()
    {
        $this->info('Importing states..');

        Excel::import(
            new UsaStatesImportSO(),
            database_path('csv/usa-states-so.csv')
        );

        $this->info('Done');

        $this->info('Importing cities..');

        Excel::import(
            new UsaCitiesImportSO(),
            Storage::disk('public')->path('zipcode/usa-cities-so.csv')
        );

        $this->info('Done');
    }
}
