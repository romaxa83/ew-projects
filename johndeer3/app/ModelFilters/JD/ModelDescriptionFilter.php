<?php

namespace App\ModelFilters\JD;

use App\Models\JD\EquipmentGroup;
use App\Repositories\JD\EquipmentGroupRepository;
use EloquentFilter\ModelFilter;

class ModelDescriptionFilter extends ModelFilter
{
    public function eg($value)
    {
        /** @var $model EquipmentGroup */
        $model = app(EquipmentGroupRepository::class)->getBy('id', $value);
        return $this->where('eg_jd_id', $model->jd_id);
    }

    public function onlyExistReport($value)
    {
        if(filter_var($value, FILTER_VALIDATE_BOOLEAN)){
            return $this->has('reportMachine');
        }

        return $this;
    }
}

