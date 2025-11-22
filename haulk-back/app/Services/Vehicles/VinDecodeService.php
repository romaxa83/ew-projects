<?php

namespace App\Services\Vehicles;

interface VinDecodeService
{
    public function decodeVin(string $vin): array;
}
