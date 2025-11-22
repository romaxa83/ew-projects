<?php

namespace App\Repositories\Saas\Company;

use App\Entities\Saas\Company\CompanyDeviceInfo;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\Company\Company;

class CompanyRepository
{
    public function getDeviceInfo($id): CompanyDeviceInfo
    {
        $model = Company::query()
            ->with(['gpsDevices'])
            ->where('id', $id)
            ->first()
        ;

        return new CompanyDeviceInfo([
            'total_device' => $model->gpsDevices->count(),
            'total_active_device' => $model->gpsDevices->countByStatus(DeviceStatus::ACTIVE()),
            'total_inactive_device' => $model->gpsDevices->countByStatus(DeviceStatus::INACTIVE()),
        ]);
    }

    public function getBy($field, $value, array $relations = []): Company
    {
        return Company::query()
            ->with($relations)
            ->where($field, $value)
            ->first()
        ;
    }
}


