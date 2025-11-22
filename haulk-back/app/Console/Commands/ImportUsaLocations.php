<?php

namespace App\Console\Commands;

use App\Imports\UsaCitiesImport;
use App\Imports\UsaStatesImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportUsaLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:import-usa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import USA locations';

    /**
     * Execute the console command.
     *
     * https://www.unitedstateszipcodes.org/zip-code-database/
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Importing states..');

        Excel::import(
            new UsaStatesImport(),
            database_path('csv/usa-states.csv')
        );

        $this->info('Done');

        $this->info('Importing cities..');

        Excel::import(
            new UsaCitiesImport(),
            database_path('csv/usa-cities.csv')
        );

        $this->info('Done');
    }
}
