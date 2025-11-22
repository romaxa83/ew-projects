<?php


namespace Database\Seeders;


use App\Imports\RegionsImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class RegionsSeeder extends Seeder
{
    public function run(): void
    {
        Excel::import(
            new RegionsImport(),
            database_path('files/regions.csv')
        );
    }
}
