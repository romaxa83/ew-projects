<?php

namespace App\Repositories\Catalog\Service;

use App\Models\Catalogs\Service\Service;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository extends AbstractRepository
{
    public function query()
    {
        return Service::query();
    }

    public function getCreditService(array $relation = [])
    {
        return $this->getByAlias(Service::CREDIT_ALIAS, $relation);
    }

    public function getBodyService(array $relation = [])
    {
        return $this->getByAlias(Service::BODY_ALIAS, $relation);
    }

    public function getSparesService(array $relation = [])
    {
        return $this->getByAlias(Service::SPARES_ALIAS, $relation);
    }

    public function getByAlias($alias, array $relation = [])
    {
        return $this->query()
            ->with($relation)
            ->where('alias', $alias)
            ->first();
    }

    public function getDataForHash()
    {
        return $this->query()
            ->select([
                'id',
                'parent_id',
                'active',
                'for_guest',
                'icon'
            ])
            ->with([
                'translations:service_id,lang,name',
                'durations:id,sort,active',
                'durations.translations:duration_id,lang,name',
                'insuranceFranchises'
            ])
            ->get()
            ->toArray()
            ;
    }

    public function getHaveRealTime(): Collection
    {
        return $this->query()->whereIn('alias', Service::haveRealDate())->get();
    }
}

