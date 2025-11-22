<?php

namespace App\Repositories\Catalog\Car;

use App\Models\Catalogs\Car\Brand;
use App\Repositories\AbstractRepository;

class BrandRepository extends AbstractRepository
{
    public function query()
    {
        return Brand::query();
    }

    public function getDataForHash()
    {
        return $this->query()
            ->with([
                'images',
                'mileages',
                'works'
            ])
            ->get()->toArray();

//        return $this->query()
//            ->select(['uuid', 'is_main', 'active', 'sort', 'name', 'color'])
//            ->get()->toArray();
    }

    public function getWorksId($brandId): array
    {
        return $this->query()
            ->with('works')
            ->where('id', $brandId)
            ->get()
            ->pluck('works.*.id')
            ->toArray()[0]
            ;
    }
    public function getSparesIdRelatedToBrand($brandId): array
    {
        return $this->query()
            ->with('sparesGroups.spares')
            ->where('id', $brandId)
            ->get()
            ->pluck('sparesGroups.*.spares.*.id')
            ->toArray()[0]
        ;
    }

    public function getMain()
    {
        return $this->query()
            ->where('is_main', true)->limit(3)
            ->get()
            ;
    }
}

