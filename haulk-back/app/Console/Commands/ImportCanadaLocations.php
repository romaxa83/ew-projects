<?php

namespace App\Console\Commands;

use App\Imports\CanadaCitiesImport;
use App\Imports\CanadaStatesImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Storage;

class ImportCanadaLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:import-canada';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Canada locations';

    /**
     * Execute the console command.
     *
     * https://www.serviceobjects.com/public/zipcode/ZipCodeFiles.zip
     */
    public function handle()
    {
        $this->info('Importing states..');

        Excel::import(
            new CanadaStatesImport(),
            database_path('csv/canada-states.csv')
        );

        $this->info('Done');

        $this->info('Importing cities..');
        Excel::import(
            new CanadaCitiesImport(),
            Storage::disk('public')->path('zipcode/canada-cities.csv')
        );

        $this->info('Done');
    }
}
