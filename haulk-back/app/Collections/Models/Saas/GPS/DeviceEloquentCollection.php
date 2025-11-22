<?php

namespace App\Collections\Models\Saas\GPS;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\GPS\Device;
use Illuminate\Database\Eloquent\Collection;

class DeviceEloquentCollection extends Collection
{
    public function countByStatus(DeviceStatus $status):int
    {
        return $this->where('status', $status)->count();
    }

    public function firstActiveDevice(): ?Device
    {
        return $this
            ->where('status', DeviceStatus::ACTIVE)
            ->sortBy('active_at')
            ->first()
            ;
    }
}

