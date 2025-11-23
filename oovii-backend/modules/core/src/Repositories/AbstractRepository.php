<?php

namespace WezomCms\Core\Repositories;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

abstract class AbstractRepository
{
    public function __construct()
    {}

    public function forSelect(
        array $relations = [],
        string $orderField = 'id',
        string $field = 'name',
        bool $active = true,
        $choiceText = false,
        string $activeField = 'active'
    ): array {
        $query = $this->query();

        $models = $query
            ->with($relations)
            ->where($activeField, $active)
            ->orderBy($orderField)
            ->get()
            ->pluck($field, 'id')
            ->toArray();

        if ($choiceText) {
            $choice[0] = $choiceText;
            $models = $choice + $models;
        }

        return $models;
    }

    abstract protected function query(): Builder;

    public function forSelectWithTranslation($withoutId = null, $choiceText = false)
    {
        $q = $this->query()
            ->with(
                [
                    'translations' => function ($query) {
                        $query->orderBy('name');
                    }
                ]
            );

        if ($withoutId) {
            $q->where("id", "!=", $withoutId);
        }

        $models = $q->get()
            ->pluck('name', 'id')
            ->toArray();

        if ($choiceText) {
            $choice[""] = $choiceText;
            $models = $choice + $models;
        }

        return $models;
    }

    public function getByID($id, array $relation = [], $withActive = false)
    {
        $query = $this->query()
            ->with($relation)
            ->where('id', $id);

        if ($withActive) {
            $query->active();
        }

        return $query->first();
    }

    public function getAll(
        array $relation = [],
        $withActive = false,
        array $filter = [],
        $published = true,
        array $order = []
    ) {
        $query = $this->query()
            ->filter($filter)
            ->with($relation);

        if ($published) {
            $query->published();
        }
        if ($withActive) {
            $query->active();
        }
        if (!empty($order)) {
            foreach ($order as $field => $type) {
                $query->orderBy($field, $type);
            }
        }

        return $query->get();
    }

    public function getAllByFieldIn(
        string $field,
        array $data = [],
        array $relation = [],
        array $filter = [],
        array $order = []
    ) {
        $query = $this->query()
            ->filter($filter)
            ->with($relation)
            ->whereIn($field, $data);

        if (!empty($order)) {
            foreach ($order as $fieldName => $type) {
                $query->orderBy($fieldName, $type);
            }
        }

        return $query->get();
    }

    public function getAllByField(
        string $field,
        string $value,
        array $relation = [],
        $withActive = false
    ) {
        $query = $this->query()
            ->with($relation)
            ->where($field, $value);

        if ($withActive) {
            $query->active();
        }

        return $query->get();
    }

    public function findByID(
        $id,
        array $relation = [],
        $withActive = false,
        $exceptionMessage = null
    ) {
        $query = $this->query()
            ->with($relation)
            ->where('id', $id);

        if ($withActive) {
            $query->active();
        }

        if ($model = $query->first()) {
            return $model;
        }

        if (is_null($exceptionMessage)) {
            $exceptionMessage = __('error.not found model');
        }

        throw new DomainException($exceptionMessage, 404);
    }

    public function existBy(
        $field,
        $value,
        $withoutId = null,
        bool $withActive = false
    ): bool {
        $q = $this->query()
            ->where($field, $value)
            ->when($withActive, fn ($query) => $query->active());

        if ($withoutId) {
            $q->where('id', '!=', $withoutId);
        }

        return $q->exists();
    }

    public function trashedFindByID(
        $id,
        array $relation = [],
        $exceptionMessage = null
    ) {
        $query = $this->query()
            ->withTrashed()
            ->with($relation)
            ->where('id', $id);


        if ($model = $query->first()) {
            return $model;
        }

        if (is_null($exceptionMessage)) {
            $exceptionMessage = __('error.not found model');
        }

        throw new DomainException($exceptionMessage, 404);
    }

    /*
     *  Get one model
     */

    public function findOneBy(
        string $field,
        string $value,
        array $relations = []
    ): Model {
        if ($model = $this->getOneBy($field, $value, $relations)) {
            return $model;
        }

        throw new DomainException(__('error.not found model'), Response::HTTP_NOT_FOUND);
    }

    public function getOneBy(
        $field,
        $value,
        array $relations = [],
        bool $withActive = false
    ): ?Model {
        return $this->getOneQuery($field, $value, $relations, $withActive)->first();
    }

    public function getOneQuery(
        string $field,
        string $value,
        array $relations = [],
        bool $withActive = false
    ): Builder {
        return $this->query()
            ->with($relations)
            ->where($field, $value)
            ->when($withActive, fn ($query) => $query->active());
    }

    public function countBy(
        string $field = null,
        string $value = null,
    ): int {
        $query = $this->query();

        if ((null != $field) && (null != $value)) {
            $query->where($field, $value);
        }

        return $query->count();
    }

    public function countAllWithFilter(
        array $filter = [],
        $withActive = false,
        $published = true,
    ): int {
        $query = $this->query()
            ->filter($filter);

        if ($published) {
            $query->published();
        }
        if ($withActive) {
            $query->active();
        }

        return $query->count();
    }

    public function getByName(string $name, $relation = [])
    {
        return $this->query()
            ->with($relation)
            ->whereHas(
                "translations",
                function ($q) use ($name) {
                    $q->where("name", $name);
                }
            )
            ->first();
    }

    public function existByName(string $name): bool
    {
        return $this->query()
            ->with('translations')
            ->whereHas(
                "translations",
                function ($q) use ($name) {
                    $q->where("name", $name);
                }
            )
            ->exist();
    }
}
