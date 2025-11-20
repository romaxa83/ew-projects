<?php

namespace App\Abstractions;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository
{
    public function __construct()
    {}

    abstract protected function query();

    public function existBy(
        $field,
        $value,
        $withoutId = null
    ): bool
    {
        $q = $this->query()->where($field, $value);

        if($withoutId){
            $q->where("id", "!=", $withoutId);
        }

        return $q->exists();
    }

    public function getBy(
        $field,
        $value,
        $relations = []
    ): ?Model
    {
        $q = $this->query()
            ->with($relations)
            ->where($field, $value);

        return $q->first();
    }

    public function findBy(
        $field,
        $value,
        $relations = [],
        string $msg = ''
    ): ?Model
    {

        $q = $this->query()
            ->with($relations)
            ->where($field, $value)
            ->first();

        if(!$q){
            if(!$msg){
                $msg = __("message.exceptions.not found", [
                    'field' => $field,
                    'value' => $value,
                ]);
            }
            throw new \DomainException($msg);
        }

        return $q;
    }

    public function getAll(
        $relations = [],
        $filters = [],
        $order = [],
        $active = false
    )
    {
        $q = $this->query()
            ->with($relations)
            ->filter($filters)
        ;

        if($active){
            $q->active();
        }

        if(!empty($order)){
            foreach ($order as $field => $type) {
                $q->orderBy($field, $type);
            }
        }

        return $q->get();
    }

    public function getAllWithoutFilters(
        $relations = [],
        $order = [],
        $active = false
    )
    {
        $q = $this->query()
            ->with($relations)
        ;

        if($active){
            $q->active();
        }

        if(!empty($order)){
            foreach ($order as $field => $type) {
                $q->orderBy($field, $type);
            }
        }

        return $q->get();
    }

    public function getAllPaginator(
        $relations = [],
        $filters = [],
        $order = [],
        $active = false
    )
    {
        $q = $this->query()
            ->with($relations)
            ->filter($filters)
        ;

        if($active){
            $q->active();
        }

        if(!empty($order)){
            foreach ($order as $field => $type) {
                $q->orderBy($field, $type);
            }
        }

        return $q->paginate($this->getPerPage($filters));
    }

    public function getAllWrap(
        $relations = [],
        $filters = [],
        $order = [],
        $active = false
    )
    {
        if(isset($filters['paginator']) && ($filters['paginator'] == false || $filters['paginator'] == "false")){
            return $this->getAll($relations, $filters, $order, $active);
        }

        return $this->getAllPaginator($relations, $filters, $order, $active);
    }

    public function getAllByLimit(
        $relations = [],
        ?int $limit = null,
        ?int $offset = null
    )
    {
        $q = $this->query()
            ->with($relations)
        ;

        if($limit){
            $q->limit($limit);
        }
        if($offset){
            $q->offset($offset);
        }

        return $q->get();
    }

    public function count($filters = []): int
    {
        return $this->query()
            ->filter($filters)
            ->count()
        ;
    }

    public function getPerPage($filters)
    {
        if(isset($filters['perPage'])){
            return $filters['perPage'];
        }
        if(isset($filters['per_page'])){
            return $filters['per_page'];
        }

        return BaseModel::DEFAULT_PER_PAGE;
    }
}

