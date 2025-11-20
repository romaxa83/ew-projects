<?php

namespace App\Resources\Custom;

use App\Models\JD\EquipmentGroup;

class CustomEgResource
{
    private $list = [];

    public function fill($data)
    {
        foreach ($data as $item){
            /** @var $item EquipmentGroup */
            $this->list[$item->id] = $item->name;
        }

        return $this->list;
    }
}
