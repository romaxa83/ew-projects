<?php

namespace App\Services\Saas\GPS\Flespi\Commands\Devices;

use App\Services\Saas\GPS\Flespi\Collections\DeviceEntityCollection;
use App\Services\Saas\GPS\Flespi\Commands\BaseGetCommand;
use App\Services\Saas\GPS\Flespi\Entities\DeviceEntity;

class DeviceGetAllCommand extends BaseGetCommand
{
    public function getUri(): string
    {
        return config("flespi.patches.all_devices");
    }

    protected function afterRequest(array $res)
    {
        $collection = new DeviceEntityCollection();
        foreach ($res['result'] ?? [] as $key => $item){
            $collection->put($key, new DeviceEntity($item));
        }

        return $collection;
    }
}
