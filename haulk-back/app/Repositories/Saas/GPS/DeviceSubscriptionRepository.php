<?php

namespace App\Repositories\Saas\GPS;

use App\Models\Saas\GPS\DeviceSubscription;

class DeviceSubscriptionRepository
{
    public function getBy(
        $field,
        $value,
        array $relations = []
    ): ?DeviceSubscription
    {
        return DeviceSubscription::query()
            ->with($relations)
            ->where($field, $value)
            ->first()
        ;
    }
}



