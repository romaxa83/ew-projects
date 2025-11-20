<?php

namespace App\DTO;

use App\Models\JD\ModelDescription;

class MdDTO
{
    private $list = [];

    public function fill($data)
    {
        foreach ($data as $item){
            /** @var $item ModelDescription */
            $this->list[$item->id] = $item->name;
        }

        return $this->list;
    }
}
