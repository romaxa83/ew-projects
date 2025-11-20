<?php

namespace WezomCms\Dealerships\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Dealerships\Models\Dealership;

class DealershipRepository extends AbstractRepository
{
    protected function query()
    {
        return Dealership::query();
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

        if(isset($params['cityId']) && !empty($params['cityId'])){
            $query->where('city_id', $params['cityId']);
        }
        if(isset($params['brandId']) && !empty($params['brandId'])){
            $query->where('brand_id', $params['brandId']);
        }

        if($this->getSearch()){

            $query->where(function(Builder $q) {
                $q->orWhereHas('translation', function($q){
                    $q->where('name', 'LIKE', $this->getSearch() . '%');
                })->orWhereHas('brand', function($q){
                    $q->where('name', 'LIKE', $this->getSearch() . '%');
                });
            });
        }

        return $query->orderBy($orderField, $typeSort)
            ->offset($this->getOffset())
            ->limit($this->getLimit())
            ->get();
    }

    public function getAllOnlyCount(
        array $relations = ['translations'],
        string $orderField = 'sort',
        array $params = [],
        bool $withPublished = true
    ): int
    {
        $query = $this->query()
            ->published()
            ->with($relations);

        if(isset($params['cityId']) && !empty($params['cityId'])){
            $query->where('city_id', $params['cityId']);
        }
        if(isset($params['brandId']) && !empty($params['brandId'])){
            $query->where('brand_id', $params['brandId']);
        }

        if($this->getSearch()){
            $query->where(function(Builder $q) {
                $q->orWhereHas('translation', function($q){
                    $q->where('name', 'LIKE', $this->getSearch() . '%');
                })->orWhereHas('brand', function($q){
                    $q->where('name', 'LIKE', $this->getSearch() . '%');
                });
            });
        }

        return $query->count();
    }

    public function forSelect(
        array $relations = ['translations', 'brand'],
        string $orderField = 'sort',
        string $field = 'name',
        bool $withPublished = true,
        $choiceText = false
    ): array
    {
        $query = $this->query();

        if($withPublished){
            $query->published();
        }

        $models = [];

        $query->with($relations)
            ->orderBy($orderField)
            ->get()
            ->map(function ($item) use(&$models){
                $models[$item->id] = $item->name_with_brand;
            });

        if($choiceText){
            $choice[0] = $choiceText;
            $models = $choice + $models;
        }

        return $models;
    }
}
