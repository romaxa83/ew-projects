<?php

namespace App\Foundations\Repositories;

use App\Foundations\Models\BaseModel;
use App\Foundations\Traits\Filters\Filterable;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 *  payload - массив , где ключ - поле в модели, а значение - значение в бд
 *  ->getByFields([
 *      'id' => 1,
 *      'name' => 'some name,
 *  ])
 *
 */
abstract readonly class BaseEloquentRepository
{
    public function __construct()
    {}

    abstract protected function modelClass(): string;

    private function eloquentBuilder(): Builder
    {
        return $this->modelClass()::query();
    }

    public function getBy(
        array $payload = [],
        array $relations = [],
        array $select = ['*'],
        array $taps = [],
        bool $withException = false,
        string $exceptionMessage = 'Model not found',
        bool $withTrashed = false
    ): null|BaseModel|Model
    {
        $query = $this->eloquentBuilder()
            ->select($select)
            ->when($withTrashed, fn(Builder $b) => $b->withTrashed())
            ->with($relations)
        ;

        foreach ($taps as $tap){
            $query->tap($tap);
        }

        foreach ($payload as $field => $value) {
            $query->where($field, $value);
        }

        $result = $query->first();

        if ($withException && null === $result) {
            throw new DomainException($exceptionMessage, Response::HTTP_NOT_FOUND);
        }

        return $result;
    }

    public function getAll(
        array $payload = [],
        array $relation = [],
        array $filters = [],
        array $select = ['*'],
        array $taps = [],
        string|array $sort = 'id',
        int|null $limit = null
    ): Collection
    {
        $query = $this->eloquentBuilder()
            ->select($select)
            ->filter($filters)
            ->with($relation);

        foreach ($taps as $tap){
            $query->tap($tap);
        }

        foreach ($payload as $field => $value) {
            $query->where($field, $value);
        }

        if(is_array($sort)){
            foreach ($sort as $field => $type) {
                $query->orderBy($field, $type);
            }
        } else {
            $query->latest($sort);
        }

        if($limit){
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getModelsBuilder(
        array $relation = [],
        array $filters = [],
        array $select = ['*'],
        array $taps = [],
        string|array $sort = 'id'
    ): Builder
    {
        $query = $this->eloquentBuilder()
            ->select($select)
            ->with($relation);

        foreach ($taps as $tap){
            $query->tap($tap);
        }

        if($this->checkFilterTrait()){
            $query->filter($filters);
        }

        if(!isset($filters['sort'])){
            if(is_array($sort)){
                foreach ($sort as $field => $type) {
                    $query->orderBy($field, $type);
                }
            } else {
                $query->latest($sort);
            }
        }

        return $query;
    }

    public function getAllPagination(
        array $relation = [],
        array $filters = [],
        array $select = ['*'],
        array $taps = [],
        string|array $sort = 'id'
    ): LengthAwarePaginator
    {
        return $this->getModelsBuilder(
            $relation,
            $filters,
            $select,
            $taps,
            $sort
        )->paginate(
            perPage: $this->getPerPage($filters),
            page: $this->getPage($filters)
        );
    }

    public function getCollection(
        array $relation = [],
        array $filters = [],
        array $select = ['*'],
        array $taps = [],
        string|array $sort = 'id'
    ): Collection
    {
        return $this->getModelsBuilder(
            $relation,
            $filters,
            $select,
            $taps,
            $sort
        )->get();
    }

    public function getList(
        array $select = ['*'],
        array $relation = [],
        array $filters = [],
        array $taps = [],
        string|array $sort = 'id'
    ): Collection
    {
        $query = $this->eloquentBuilder()
            ->select($select)
            ->with($relation)
        ;

        foreach ($taps as $tap){
            $query->tap($tap);
        }

        if($this->checkFilterTrait()){
            $query->filter($filters);
        }

        if(is_array($sort)){
            foreach ($sort as $field => $type) {
                $query->orderBy($field, $type);
            }
        } else {
            $query->latest($sort);
        }

        return $query->get();
    }

    public function countBy(array $payload = []): int
    {
        $query = $this->eloquentBuilder();

        foreach ($payload as $field => $value) {
            $query->where($field, $value);
        }

        return $query->count();
    }

    public function existBy(array $payload = []): bool
    {
        $query = $this->eloquentBuilder();

        foreach ($payload as $field => $value) {
            $query->where($field, $value);
        }

        return $query->exists();
    }

    public function getPerPage($filters): int
    {
        if(isset($filters['per_page'])){
            return $filters['per_page'];
        }

        return BaseModel::DEFAULT_PER_PAGE;
    }

    public function getPage($filters): int
    {
        if(isset($filters['page'])){
            return $filters['page'];
        }

        return 1;
    }

    private function checkFilterTrait(): bool
    {
        return array_key_exists(Filterable::class, class_uses($this->modelClass()));
    }
}

