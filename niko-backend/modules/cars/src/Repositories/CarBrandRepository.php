<?php

namespace WezomCms\Cars\Repositories;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Cars\Models\Brand;
use WezomCms\Core\Repositories\AbstractRepository;

class CarBrandRepository extends AbstractRepository
{
    protected function query()
    {
        return Brand::query();
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

        $query = $this->query();

        if($withPublished){
            $query->published();
        }

        $query->with($relations);

        if($this->getSearch()){

            if(in_array('translations', $relations)){
                $query->whereHas('translation', function($q){
                    $q->where('name', 'LIKE', '%' . $this->getSearch() . '%');
                });
            } else {
                $query->where('name', 'LIKE', '%' . $this->getSearch() . '%');
            }
        }

        if(isset($params['maintainable']) && filter_var($params['maintainable'], FILTER_VALIDATE_BOOLEAN)){
            $query->whereHas('dealership');
        }

        if(isset($params['cityId'])){
            $cityId = $params['cityId'];
            $query->whereHas('dealership', function($q) use ($cityId){
                $q->where('city_id', $cityId);
            });
        }

        return $query->orderBy($orderField, $typeSort)
            ->offset($this->getOffset())
            ->limit($this->getLimit())
            ->get();
    }
}
