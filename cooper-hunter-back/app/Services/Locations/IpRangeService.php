<?php

namespace App\Services\Locations;

use App\Imports\States\IpRangeImport;
use App\Services\Excel\Excel;

class IpRangeService
{
    public function import(): void
    {
        $file = database_path('files/csv/us-ip2location-lite-db9.csv');

        Excel::import(new IpRangeImport(), $file);
    }
}
