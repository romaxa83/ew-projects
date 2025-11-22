<?php

namespace App\ModelFilters\Saas\GPS\Devices;

use EloquentFilter\ModelFilter;

class DeviceRequestFilter extends ModelFilter
{
    public function company(int $companyId): void
    {
        $this->where('company_id', $companyId);
    }

    public function status(string $value): void
    {
        $this->where('status', $value);
    }
}

