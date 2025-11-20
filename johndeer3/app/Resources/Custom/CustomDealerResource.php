<?php

namespace App\Resources\Custom;

use App\Models\JD\Dealer;
use App\Type\StatisticType;

class CustomDealerResource
{
    private $list = [];

    public function forStatistics()
    {
        $this->list[StatisticType::ALL] = __('translates.all');

        return $this;
    }

    public function fill($data)
    {
        foreach ($data as $item){
            /** @var $item Dealer */
            $this->list[$item->id] = $item->name;
        }

        return $this->list;
    }
}

