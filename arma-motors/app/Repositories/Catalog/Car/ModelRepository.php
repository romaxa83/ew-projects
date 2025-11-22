<?php

namespace App\Repositories\Catalog\Car;

use App\Models\Catalogs\Car\Model;
use App\Repositories\AbstractRepository;

class ModelRepository extends AbstractRepository
{
    public function query()
    {
        return Model::query();
    }

    public function getDataForHash()
    {
        return $this->query()
            ->with('images')
            ->get()->toArray();

//        return $this->query()
//            ->select(['uuid', 'active', 'sort', 'name', 'brand_id', 'for_credit', 'for_calc'])
//            ->get()->toArray();
    }
}
