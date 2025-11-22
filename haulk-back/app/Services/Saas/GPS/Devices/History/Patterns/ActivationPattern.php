<?php

namespace App\Services\Saas\GPS\Devices\History\Patterns;

use App\Enums\BaseEnum;
use App\Enums\Saas\GPS\DeviceHistoryType;
use App\Models\Saas\GPS\Device;
use App\Services\Saas\GPS\Devices\History\BaseDeviceHistoryService;

class ActivationPattern extends BaseDeviceHistoryService
{
    private Device $model;

    public function __construct(Device $model)
    {
        $this->model = $model;
    }

    public function data(): array
    {
        return [
            'device_id' => $this->model->id,
            'type' => DeviceHistoryType::ACTIVATION,
            'changed_data' => $this->getChanged()
        ];
    }

    private function getChanged(): array
    {
        $data = [];
        if(!empty($this->model->getDirty())){
            $data['new'] = $this->model->getDirty();
            foreach ($this->model->getDirty() as $field => $value){
                $prettyValue = $this->model->getOriginal()[$field];

                if($this->model->getOriginal()[$field] instanceof BaseEnum){
                    $prettyValue = $this->model->getOriginal()[$field]->value;
                }

                $data['old'][$field] = $prettyValue;
            }
        }

        return $data;
    }
}
