<?php

namespace App\Repositories;

use App\Models\BaseAuthenticatable;
use App\Models\BaseModel;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
abstract class AbstractRepository
{
    public function __construct()
    {
    }

    abstract protected function modelQuery(): Builder|BaseModel;

    public function getBy(
        $field,
        $value,
        array $relations = [],
        $withException = false,
        $exceptionMessage = 'Model not found'
    ): null|BaseModel|BaseAuthenticatable
    {
        $result = $this->modelQuery()
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
    ): ?BaseModel
    {
        $query = $this->modelQuery()
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
        $query = $this->modelQuery()->select($select);

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
        $query = $this->modelQuery()
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
        $query = $this->modelQuery()
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
        $query = $this->modelQuery()
            ->with($relation);

        foreach ($payload as $field => $value) {
            $query->where($field, $value);
        }

        return $query->get();
    }

    public function getAllWhereIn(
        array $payload = [],
        array $relation = [],
    ): Collection
    {
        $query = $this->modelQuery()
            ->with($relation);

        foreach ($payload as $field => $value) {
            $query->whereIn($field, $value);
        }

        return $query->get();
    }

    public function getAllPagination(
        array $relation = [],
        array $filters = [],
        string|array $sort = 'id'
    ): LengthAwarePaginator
    {
        $query = $this->modelQuery()
            ->filter($filters)
            ->with($relation)
        ;

        if(is_array($sort)){
            foreach ($sort as $field => $type) {
                $query->orderBy($field, $type);
            }
        } else {
            $query->latest($sort);
        }

        return $query->paginate(
            perPage: $this->getPerPage($filters),
            page: $this->getPage($filters)
        );
    }

    public function countBy(array $payload = []): int
    {
        $query = $this->modelQuery();

        foreach ($payload as $field => $value) {
            $query->where($field, $value);
        }

        return $query->count();
    }

    public function existBy(array $payload = []): bool
    {
        $query = $this->modelQuery();

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
}
