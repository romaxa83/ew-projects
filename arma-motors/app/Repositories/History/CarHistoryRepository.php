<?php

namespace App\Repositories\History;

use App\Models\History\CarItem;
use App\Repositories\AbstractRepository;

class CarHistoryRepository extends AbstractRepository
{
    public function query()
    {
        return CarItem::query();
    }
}
