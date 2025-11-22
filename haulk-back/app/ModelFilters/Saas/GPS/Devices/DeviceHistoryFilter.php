<?php

namespace App\ModelFilters\Saas\GPS\Devices;

use EloquentFilter\ModelFilter;

class DeviceHistoryFilter extends ModelFilter
{
    public function device(int $value): void
    {
        $this->where('device_id', $value);
    }
}


