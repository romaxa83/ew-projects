<?php

namespace Database\Seeders;

use App\Imports\TireMakesAndModelsImport;
use App\Imports\TireSizesImport;
use App\Imports\VehicleClassesAndTypesImport;
use App\Imports\VehicleMakesAndModelsImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class DictionariesSeeder extends Seeder
{
    public function run(): void
    {
        Excel::import(
            new VehicleMakesAndModelsImport(),
            database_path('files/VehicleMakesAndModels.csv')
        );

        Excel::import(
            new VehicleClassesAndTypesImport(),
            database_path('files/VehicleClassesAndTypes.csv')
        );

        Excel::import(
            new TireMakesAndModelsImport(),
            database_path('files/TireMakesAndModels.csv')
        );

        Excel::import(
            new TireSizesImport(),
            database_path('files/TireSizes.csv')
        );
    }
}
