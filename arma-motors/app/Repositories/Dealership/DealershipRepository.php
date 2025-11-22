<?php

namespace App\Repositories\Dealership;

use App\Models\Dealership\Dealership;
use App\Repositories\AbstractRepository;

class DealershipRepository extends AbstractRepository
{
    public function query()
    {
        return Dealership::query();
    }

    public function getDataForHash()
    {
        return $this->query()
            ->with([
                'translations',
                'departments',
                'departments.translations',
                'departments.schedule',
                'images'
            ])
            ->get()
            ->toArray()
            ;

//        return $this->query()
//            ->select([
//                'id',
//                'brand_id',
//                'website',
//                'active',
//                'sort',
//                'location'
//            ])
//            ->with([
//                'translations:dealership_id,lang,name,text,address',
////                'departments:dealership_id,id,sort,active,phone,email,telegram,viber,type,location',
////                'departments.translations:department_id,lang,name,address',
////                'departments.schedule:department_id,day,from,to',
//                'images:entity_id'
//            ])
//            ->get()
//            ->toArray()
//            ;
    }
}

