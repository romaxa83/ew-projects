<?php

namespace App\Services\Saas\GPS\Devices\History;

use App\Models\Saas\GPS\DeviceHistory;

abstract class BaseDeviceHistoryService implements DeviceHistoryPatternContract
{
    abstract function data(): array;

    public function create(): DeviceHistory
    {
        $model = new DeviceHistory();
        $model->device_id = data_get($this->data(), 'device_id');
        $model->type = data_get($this->data(), 'type');
        $model->changed_data = data_get($this->data(), 'changed_data', []);

        $model->save();

        return $model;
    }
}


