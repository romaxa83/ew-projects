<?php

namespace App\Services\Saas\GPS\Devices\History;

use App\Models\Saas\GPS\DeviceHistory;

interface DeviceHistoryPatternContract
{
    public function create(): DeviceHistory;
}



