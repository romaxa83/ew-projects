<?php

namespace App\Repositories;

use App\Models\BaseModel;
use App\Traits\Filterable;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 *  payload - массив , где ключ - поле в модели, а значение - значение в бд
 *  ->getByFields([
 *      ['id' => 1],
 *      ['name' => 'some name],
 *  ])
 *
 */
abstract class AbstractRepository implements RepositoryInterface
{
    public function __construct()
    {}

    abstract protected function modelClass(): string;

    private function eloquentBuilder(): Builder
    {
        return $this->modelClass()::query();
    }

    public function getBy(
        $field,
        $value,
        array $relations = [],
        $withException = false,
        $exceptionMessage = 'Model not found'
    ): null|BaseModel|Model
    {
        $result = $this->eloquentBuilder()
            ->with($relations)
            ->where($field, $value)
            ->first()
        ;

        if ($withException && null === $result) {
            throw new DomainException($exceptionMessage, Response::HTTP_NOT_FOUND);
        }

        return $result;
    }

    public function getByFields(
        array $payload = [],
        array $relation = [],
        $withException = false,
        $exceptionMessage = 'Model not found'
    ): null|BaseModel|Model
    {
        $query = $this->eloquentBuilder()
            ->with($relation);

        foreach ($payload as $field => $value) {
            $query->where($field, $value);
        }

        $result = $query->first();

        if ($withException && null === $result) {
            throw new DomainException($exceptionMessage, Response::HTTP_NOT_FOUND);
        }

        return $result;
    }

    public function getByFieldsObj(
        array $payload = [],
        array $select = ['*'],
        $withException = false,
        $exceptionMessage = 'Model not found'
    ): ?object
    {
        $query = $this->eloquentBuilder()->select($select);

        foreach ($payload as $field => $value) {
            $query->where($field, $value);
        }

        $result = $query->toBase()->first();

        if ($withException && null === $result) {
            throw new DomainException($exceptionMessage, Response::HTTP_NOT_FOUND);
        }

        return $result;
    }

    public function getAll(
        array $relation = [],
        array $filters = [],
        $onlyActive = false,
        string|array $sort = 'id'
    ) {
        $query = $this->eloquentBuilder()
            ->filter($filters)
            ->with($relation);

        if ($onlyActive) {
            $query->active();
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

    public function getAllObj(
        array $select = ['*'],
        array $relation = [],
        array $filters = [],
        string|array $sort = 'id'
    ) {
        $query = $this->eloquentBuilder()
            ->select($select)
            ->filter($filters)
            ->with($relation);

        if(is_array($sort)){
            foreach ($sort as $field => $type) {
                $query->orderBy($field, $type);
            }
        } else {
            $query->latest($sort);
        }

        return $query->toBase()->get();
    }

    public function getAllByFields(
        array $payload = [],
        array $relation = [],
    ): Collection
    {
        $query = $this->eloquentBuilder()
            ->with($relation);

        foreach ($payload as $field => $value) {
            if(is_array($value)){
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->get();
    }

    public function getAllBy(
        string $field = 'id',
        array $data = [],
        array $relation = [],
    ): Collection
    {
        return $this->eloquentBuilder()
            ->with($relation)
            ->whereIn($field, $data)
            ->get()
        ;
    }

    public function getModelsBuilder(
        array $relation = [],
        array $filters = [],
        string|array $sort = 'id'
    ): Builder
    {
        $query = $this->eloquentBuilder()
            ->with($relation);

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

    public function getPagination(
        array $relation = [],
        array $filters = [],
        string|array $sort = 'id'
    ): LengthAwarePaginator
    {
        return $this->getModelsBuilder(
            $relation,
            $filters,
            $sort
        )->paginate(
            perPage: $this->getPerPage($filters),
            page: $this->getPage($filters)
        );
    }

    public function getCollection(
        array $relation = [],
        array $filters = [],
        string|array $sort = 'id'
    ): Collection
    {
        return $this->getModelsBuilder(
            $relation,
            $filters,
            $sort
        )->get();
    }

    public function getList(
        array $relation = [],
        array $filters = [],
        string|array $sort = 'id'
    ): Collection
    {
        $query = $this->eloquentBuilder()
            ->with($relation)
        ;

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
