<?php

namespace App\Console\Commands;

use App\Imports\UsaCitiesImportZCD;
use App\Imports\UsaStatesImportSO;
use App\Models\Locations\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Storage;

class ImportUsaLocationsZCD extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:import-usa-zcd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import USA locations from ZIP Code Database';

    /**
     * Execute the console command.
     *
     * @link https://www.unitedstateszipcodes.org/zip-code-database/
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

        $this->createTempTable();

        Excel::import(
            new UsaCitiesImportZCD(),
            Storage::disk('public')->path('zipcode/usa-cities-zcd.csv')
        );

        $this->info('Temp table has been loaded');

        $this->importDataFromTempTable();

        $this->info('Done');
    }

    private function createTempTable()
    {
        Schema::dropIfExists('cities_temp');

        DB::insert(DB::raw("CREATE TEMPORARY TABLE cities_temp AS SELECT * FROM cities WITH NO DATA"));

        $this->info('Temp table has been created');
    }

    private function importDataFromTempTable()
    {
        City::where('country_code', 'us')->delete();

        $this->info('Old data has been deleted');

        City::insertUsing([
            'name',
            'zip',
            'status',
            'state_id',
            'timezone',
            'country_code',
            'country_name'
        ], DB::table('cities_temp')->select([
            'name',
            'zip',
            'status',
            'state_id',
            'timezone',
            'country_code',
            'country_name'
        ]));

        $this->info('Data from temp table has been inserted');

        Schema::dropIfExists('cities_temp');

        $this->info('Temp table has been dropped');
    }
}
