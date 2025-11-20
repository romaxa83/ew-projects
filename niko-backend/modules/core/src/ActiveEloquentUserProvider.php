<?php

namespace WezomCms\Core;

use Illuminate\Auth\EloquentUserProvider;

class ActiveEloquentUserProvider extends EloquentUserProvider
{
    /**
     * Get a new query builder for the model instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newModelQuery($model = null)
    {
        $query = parent::newModelQuery($model);

        $query->where('active', true);

        return $query;
    }
}
