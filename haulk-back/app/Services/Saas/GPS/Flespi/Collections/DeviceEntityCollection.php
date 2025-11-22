<?php

namespace App\Services\Saas\GPS\Flespi\Collections;

use App\Services\Saas\GPS\Flespi\Entities\DeviceEntity;
use ArrayIterator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method DeviceEntity|null first(callable $callback = null, $default = null)
 * @method DeviceEntity|null last(callable $callback = null, $default = null)
 * @method DeviceEntity|null pop()
 * @method DeviceEntity|null shift()
 * @method ArrayIterator|DeviceEntity[] getIterator()
 */
class DeviceEntityCollection extends Collection
{
    public function filterImei(array $imei): self
    {
        return $this->filter(function (DeviceEntity $item) use ($imei) {
            return !array_key_exists($item->imei, $imei);
        });
    }
}

