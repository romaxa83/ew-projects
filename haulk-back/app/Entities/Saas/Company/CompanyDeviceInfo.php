<?php

namespace App\Entities\Saas\Company;

class CompanyDeviceInfo
{
    public int $totalDevice;
    public int $totalActiveDevice;
    public int $totalInactiveDevice;

    public function __construct(array $data)
    {
        $this->totalDevice = data_get($data, 'total_device');
        $this->totalActiveDevice = data_get($data, 'total_active_device');
        $this->totalInactiveDevice = data_get($data, 'total_inactive_device');
    }
}
