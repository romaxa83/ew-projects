<?php

namespace App\Services\Locations;

use App\Imports\States\ZipcodesImport;
use App\Services\Excel\Excel;

class ZipcodeService
{
    public function import(): void
    {
        $file = database_path('files/csv/uszips.csv');

        Excel::import(new ZipcodesImport(), $file);
    }
}
