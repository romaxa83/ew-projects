<?php

namespace WezomCms\Core\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use WezomCms\Core\Traits\Model\OrderBySort;

class GetForSelectScope implements Scope
{
    /**
     * @param  Builder  $builder
     * @param  Model  $model
     */
    public function apply(Builder $builder, Model $model)
    {
        //
    }

    /**
     * @param  Builder  $builder
     */
    public function extend(Builder $builder)
    {
        $builder->macro(
            'getForSelect',
            function (Builder $builder, bool $placeholder = false, $value = 'name', $key = 'id'): array {
                if ($builder->hasNamedScope('sorting')) {
                    /** @var OrderBySort $builder */
                    $builder->sorting();
                }

                if (!is_string($value) && is_callable($value)) {
                    $result = $value($builder->get());
                } else {
                    $result = $builder->get()->pluck($value, $key);
                }

                if ($placeholder) {
                    $result->prepend(__('cms-core::admin.layout.Not set'), '');
                }

                return $result->toArray();
            }
        );
    }
}
