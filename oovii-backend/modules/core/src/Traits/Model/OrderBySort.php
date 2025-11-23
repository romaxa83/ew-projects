<?php

namespace WezomCms\Core\Traits\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait OrderBySort
 * @package WezomCms\Core\Traits\Model
 * @property string|string[] $sortField
 * @method string|string[] sortField()
 * @method static Builder|Model sorting()
 */
trait OrderBySort
{
    /**
     * @param  Builder|mixed  $builder
     * @return Builder
     */
    public function scopeSorting($builder)
    {
        /** @var Model|OrderBySort $model */
        $model = $builder->getModel();

        switch (true) {
            case method_exists($model, 'sortField'):
                $sortField = $model->sortField();
                break;
            case property_exists($model, 'sortField'):
                $sortField = $model->sortField;
                break;
            default:
                $sortField = 'sort';
        }

        $isTranslatable = method_exists($model, 'translate');
        foreach ((array) $sortField as $field => $direction) {
            if (is_integer($field)) {
                $field = $direction;
                $direction = 'asc';
            }
            if ($isTranslatable && $model->isTranslationAttribute($field)) {
                $builder->orderByTranslation($field, $direction);
            } else {
                $builder->orderBy($field, $direction);
            }
        }

        return $builder->orderByDesc("{$model->getTable()}.{$model->getKeyName()}");
    }
}
