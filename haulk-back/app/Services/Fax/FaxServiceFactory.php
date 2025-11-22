<?php

namespace App\Services\Fax;

use App\Services\Fax\Drivers\FaxDriver;
use Illuminate\Foundation\Application;

class FaxServiceFactory
{
    public function create(Application $application): FaxDriver
    {
        $driver = config('fax.driver');

        /** @var FaxDriver $driverClass */
        $driverClass = config('fax.drivers.' . $driver);

        return $driverClass::create($application);
    }
}
