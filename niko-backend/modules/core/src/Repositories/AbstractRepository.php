<?php

namespace WezomCms\Core\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @method array formData($obj, array $viewData): array
 * @method array createViewData($obj, array $viewData): array
 * @method array editViewData($obj, array $viewData): array
 * @method array showViewData($obj, array $viewData): array
 *
 *
 * @method bySelect($relations, $orderField, $field)
 * @method afterSuccessfulUpdate($obj, FormRequest $request)
 *
 * @method beforeDelete($obj, bool $force = false)
 * @method title($obj, bool $force = false)
 */
abstract class AbstractRepository
{
    private int $limit;
    private int $offset;
    private $search;

    public function __construct()
    {
        $this->limit = config('cms.core.api.default_limit');
        $this->offset = config('cms.core.api.default_offset');
    }

    abstract protected function query();

    public function setLimit($limit)
    {
        if(is_array($limit)){
            if(array_key_exists(config('cms.core.api.limit_as'), $limit)){
                $this->limit = $limit[config('cms.core.api.limit_as')];
            }
        }

        if(is_numeric($limit)){
            $this->limit = (int)$limit;
        }
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setOffset($offset)
    {
        if(is_array($offset)){
            if(array_key_exists(config('cms.core.api.offset_as'), $offset)){

                $off = $offset[config('cms.core.api.offset_as')];
                if((int)$off > 0){
                    $this->offset = ((int)$off - 1) * $this->limit;
                }
            }
        }

        if(is_numeric($offset)){
            $this->offset = (int)$offset;
        }
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setSearch($search)
    {
        if(is_array($search)){
            if(array_key_exists(config('cms.core.api.search_as'), $search)){
                $this->search = $search[config('cms.core.api.search_as')];
            }
        }

        if(is_string($search)){
            $this->search = $search;
        }
    }

    public function getSearch()
    {
        return $this->search;
    }

    public function initParams(array $params)
    {
        $this->setLimit($params);
        $this->setOffset($params);
        $this->setSearch($params);
    }


    public function forSelect(
        array $relations = ['translations'],
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

        $models = $query
            ->with($relations)
            ->orderBy($orderField)
            ->get()
            ->pluck($field, 'id')
            ->toArray();

        if($choiceText){
            $choice[0] = $choiceText;
            $models = $choice + $models;
        }

        return $models;
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

        return $query->orderBy($orderField, $typeSort)
            ->offset($this->getOffset())
            ->limit($this->getLimit())
            ->get();
    }

    public function byId(
        int $id,
        array $relations = ['translations'],
        string $orderField = 'sort',
        bool $withPublished = true
    )
    {
        $query = $this->query();

        if($withPublished){
            $query->published();
        }

        return $query
            ->with($relations)
            ->where('id', $id)
            ->orderBy($orderField)
            ->first();
    }

    public function count($withPublished = true): int
    {
        $query = $this->query();

        if($withPublished){
            $query->published();
        }

        return $query->count();
    }
}
