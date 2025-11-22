<?php

namespace Database\Seeders;

use App\Imports\Spares\SparesImportManager;
use App\Models\Catalogs\Calc\Spares;

class SparesSeeder extends BaseSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('spares')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $pathToFile = __DIR__ . '/_spares_volvo.xlsx';
        (new SparesImportManager($pathToFile, Spares::TYPE_VOLVO))->handle();

        $pathToFile = __DIR__ . '/_spares_mits.xlsx';
        (new SparesImportManager($pathToFile, Spares::TYPE_MITSUBISHI))->handle();

        $pathToFile = __DIR__ . '/_spares_renault.xlsx';
        (new SparesImportManager($pathToFile, Spares::TYPE_RENAULT))->handle();

    }
}

