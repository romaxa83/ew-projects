<?php

namespace App\Repositories\JD;

use App\Abstractions\AbstractRepository;
use App\Models\JD\Manufacturer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ManufacturerRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Manufacturer::query();
    }

    public function getForHash(): Collection
    {
        return \DB::table(Manufacturer::TABLE)
            ->select([
                'name',
                'is_partner_jd',
                'status',
            ])
            ->get()
            ;
    }

//    public function deleteAll()
//    {
//        return $this->query()->delete();
//    }
}
