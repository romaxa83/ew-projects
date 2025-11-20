<?php

namespace WezomCms\Cars\Repositories;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Cars\Models\Model;
use WezomCms\Core\Repositories\AbstractRepository;

class ModelRepository extends AbstractRepository
{
    protected function query()
    {
        return Model::query();
    }

    public function getAllByBrand(
        $brandId,
        array $relations = ['translations'],
        string $orderField = 'sort',
        array $params = []
    ): Collection
    {

        $query = $this->query();

        $query->with($relations)->where('car_brand_id', $brandId);

        if(isset($params['maintainable']) && filter_var($params['maintainable'], FILTER_VALIDATE_BOOLEAN)){
            $query->where('for_trade', true);
        }

        return $query->orderBy($orderField, 'desc')->get();
    }

    public function getAllForTrade(
        array $relations = ['translations'],
        string $orderField = 'sort'
    ): Collection
    {

        $query = $this->query();

        $query->with($relations)->where('for_trade', true);

        return $query->orderBy($orderField, 'desc')->get();
    }
}
