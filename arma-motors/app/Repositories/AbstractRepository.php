<?php

namespace App\Repositories;

use App\Exceptions\ErrorsCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

abstract class AbstractRepository
{
    public function __construct()
    {}

    abstract protected function query();

    public function getByID($id, array $relation = [], $withActive = false)
    {
        $query = $this->query()
            ->with($relation)
            ->where('id', $id);

        if($withActive){
            $query->active();
        }

        return $query->first();
    }

    public function getAll(array $relation = [], $withActive = false)
    {
        $query = $this->query()
            ->with($relation);

        if($withActive){
            $query->active();
        }

        return $query->get();
    }

    public function getAllByField(
        string $field,
        string $value,
        array $relation = [],
        $withActive = false
    )
    {
        $query = $this->query()
            ->with($relation)
            ->where($field, $value)
        ;

        if($withActive){
            $query->active();
        }

        return $query->get();
    }

    public function findByID(
        $id,
        array $relation = [],
        $withActive = false,
        $exceptionMessage = null
    )
    {
        $query = $this->query()
            ->with($relation)
            ->where('id', $id);

        if($withActive){
            $query->active();
        }

        if($model = $query->first()){
            return $model;
        }

        if(null == $exceptionMessage){
            $exceptionMessage = __('error.not found model');
        }

        throw new \DomainException($exceptionMessage, ErrorsCode::NOT_FOUND);
    }

    public function trashedFindByID(
        $id,
        array $relation = [],
        $exceptionMessage = null
    )
    {
        $query = $this->query()
            ->withTrashed()
            ->with($relation)
            ->where('id', $id);


        if($model = $query->first()){
            return $model;
        }

        if(null == $exceptionMessage){
            $exceptionMessage = __('error.not found model');
        }

        throw new \DomainException($exceptionMessage, ErrorsCode::NOT_FOUND);
    }

    /*
     *  Get one model
     */

    public function getOneQuery(
        string $field,
        string $value,
        array $relations = []
    ): Builder
    {
        return $this->query()
            ->with($relations)
            ->where($field, $value);
    }

    public function getOneBy(
        $field,
        $value,
        array $relations = []
    ): ?Model
    {
        return $this->getOneQuery($field, $value, $relations)->first();
    }

    public function findOneBy(
        string $field,
        string $value,
        array $relations = []
    ): Model
    {
        if($model = $this->getOneBy($field, $value, $relations)){
            return $model;
        }

        throw new \DomainException(__('error.not found model'), Response::HTTP_NOT_FOUND);
    }

    public function countBy(
        string $field = null,
        string $value = null,
    ): int
    {
        $query = $this->query();

        if((null != $field) && (null != $value)){
            $query->where($field, $value);
        }

        return $query->count();
    }

    public function existBy(
        string $field = null,
        string $value = null,
    ): bool
    {
        $query = $this->query();

        if((null != $field) && (null != $value)){
            $query->where($field, $value);
        }

        return $query->exists();
    }
}
