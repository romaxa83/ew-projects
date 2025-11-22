<?php

namespace Tests\Fake\Services\Vehicles;

use App\Services\Vehicles\VinDecodeService;

class FakeVinDecodeService implements VinDecodeService
{

    public function decodeVin(string $vin): array
    {
        return [
            'make' => null,
            'model' => null,
            'year' => null,
            'type_id' => null,
        ];
    }
}
