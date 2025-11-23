<?php

namespace WezomCms\Core\Traits;

use Illuminate\Database\Eloquent\Model;

trait RecursiveBreadcrumbsTrait
{
    /**
     * @param  Model  $model
     * @param  string  $relation
     * @param  bool  $published
     */
    protected function addRecursiveBreadcrumbs(Model $model, $relation = 'parent', bool $published = true)
    {
        $result = collect([$model]);

        while ($model->{$relation . '_id'} > 0) {
            $query = $model->{$relation}();
            if ($published) {
                $query->published();
            }
            $parent = $query->first();
            if (!$parent) {
                break;
            }

            $result->push($parent);
            $model = $parent;
        }

        foreach ($result->reverse() as $model) {
            $this->addBreadcrumb($model->name, $model->getFrontUrl());
        }
    }
}
