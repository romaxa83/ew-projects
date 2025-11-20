<?php

namespace WezomCms\Services\Repositories;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Regions\Models\City;
use WezomCms\Services\Models\ServiceGroup;
use WezomCms\Services\Types\ServiceType;

class ServiceGroupRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function query()
    {
        return ServiceGroup::query();
    }

    public function getServicesByType(ServiceType $type, array $relations = ['services'])
    {
        $query = $this->query()
            ->published()
            ->with($relations)
            ->where('type', $type->type())
            ->first();

        return $query->services;
    }

    public function getServiceGroupByType(ServiceType $type)
    {
        return $this->query()
            ->where('type', $type->type())
            ->first();
    }

    public function getIdByTypes(array $types)
    {
        return $this->query()->whereIn('type', $types)->get()->pluck('id')->toArray();
    }
}

