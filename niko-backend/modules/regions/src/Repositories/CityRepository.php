<?php

namespace WezomCms\Regions\Repositories;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Regions\Models\City;

class CityRepository extends AbstractRepository
{
    private RegionsRepository $regionsRepository;

    public function __construct(RegionsRepository $regionsRepository)
    {
        parent::__construct();

        $this->regionsRepository = $regionsRepository;
    }

    protected function query()
    {
        return City::query();
    }

    public function forSelect(
        array $relations = ['translations'],
        string $orderField = 'sort',
        string $field = 'name',
        bool $withPublished  = true,
        $choiceText = false
    ): array
    {
        $data = [];

        $regions = $this->regionsRepository->forSelect();

        $cities = $this->query()
            ->published()
            ->with([
                'translations' => function ($query) {
                    $query->orderBy('name');
                }
            ])
            ->orderBy('sort')
            ->get()
            ->toArray();

        foreach ($regions as $id => $name){
            $cityForRegion = [];
            foreach ($cities as $city){
                if($id == $city['region_id']){
                    $cityForRegion[$city['id']] = $city['name'];
                }
            }
            $data[$name] = $cityForRegion;
        }

        return $data;
    }

    public function getAll(
        array $relations = ['translations'],
        string $orderField = 'sort',
        array $params = [],
        bool $withPublished = true,
        string $typeSort = 'asc'
    ): Collection
    {
        $this->initParams($params);

        $query = $this->query()
            ->published()
            ->with($relations);

        if($this->getSearch()){
            $query->whereHas('translation', function($q){
                $q->where('name', 'LIKE', $this->getSearch() . '%');
            });
        }

        $query->whereHas('dealership');

        return $query->orderBy($orderField, $typeSort)
            ->offset($this->getOffset())
            ->limit($this->getLimit())
            ->get();
    }

    public function countByRequest(array $params = []): int
    {
        $this->initParams($params);

        $query = $this->query()->published();

        if($this->getSearch()){
            $query->whereHas('translation', function($q){
                $q->where('name', 'LIKE', $this->getSearch() . '%');
            });
        }

        $query->whereHas('dealership');

        return $query->count();
    }
}
