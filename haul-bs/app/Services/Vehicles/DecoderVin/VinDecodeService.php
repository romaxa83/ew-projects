<?php

namespace App\Services\Vehicles\DecoderVin;

interface VinDecodeService
{
    public function decodeVin(string $vin): array;
}
