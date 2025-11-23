<?php

namespace WezomCms\Core\Traits\Model;

use Illuminate\Database\Eloquent\Builder;

trait PrevNextTrait
{
    /**
     * @param  callable|null  $filter
     * @return Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getPrev(callable $filter = null)
    {
        /** @var Builder $query */
        $query = (new static())->newQuery();

        $fields = (array) $this->getSortField();
        $type = $this->getSortType();

        $query->where($this->primaryKey, '!=', $this->{$this->primaryKey});

        foreach ($fields as $field) {
            if ($field !== $this->primaryKey) {
                $query->where($field, '<=', $this->{$field});
            }
        }

        foreach ($fields as $field) {
            $query->orderBy($field, $type);
        }

        $this->filterPrevNextSelection($query);

        if (is_callable($filter)) {
            call_user_func($filter, $query);
        }

        return $query->first();
    }

    /**
     * @param  callable|null  $filter
     * @return Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getNext(callable $filter = null)
    {
        /** @var Builder $query */
        $query = (new static())->newQuery();

        $fields = (array) $this->getSortField();
        $type = mb_strtolower($this->getSortType()) === 'asc' ? 'DESC' : 'ASC';

        $query->where($this->primaryKey, '!=', $this->{$this->primaryKey});

        foreach ($fields as $field) {
            if ($field !== $this->primaryKey) {
                $query->where($field, '>=', $this->{$field});
            }
        }

        foreach ($fields as $field) {
            $query->orderBy($field, $type);
        }

        $this->filterPrevNextSelection($query);

        if (is_callable($filter)) {
            call_user_func($filter, $query);
        }

        return $query->first();
    }

    /**
     * @param  Builder  $query
     */
    protected function filterPrevNextSelection(Builder $query)
    {
        //
    }

    /**
     * @return string|array
     */
    protected function getSortField()
    {
        return 'id';
    }

    /**
     * @return string
     */
    protected function getSortType()
    {
        return 'ASC';
    }
}
