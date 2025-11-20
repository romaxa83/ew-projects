<?php

namespace WezomCms\Core\Traits\Model;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait GetForSelectTrait
 * @property array $selectOptions - Selection options for GetForSelectTrait.
 */
trait GetForSelectTrait
{
    /**
     * @param  bool  $withNotSet
     * @param  callable|null  $callback
     * @return array
     */
    public static function getForSelect(bool $withNotSet = true, callable $callback = null)
    {
        $options = [
            'name_key' => 'name',
            'value_key' => 'id',
            'sort' => 'id'
        ];
        if (isset(static::$selectOptions)) {
            $options = array_merge($options, static::$selectOptions);
        }

        /** @var Builder $query */
        $query = static::select();

        $obj = new static();
        $isTranslatable = method_exists($obj, 'translate');

        $sort = $options['sort'];

        if (is_array($sort)) {
            foreach ($sort as $field => $direction) {
                if ($isTranslatable && $obj->isTranslationAttribute($field)) {
                    $query->orderByTranslation($field, $direction);
                } else {
                    $query->orderBy($field, $direction);
                }
            }
        } else {
            if ($isTranslatable && $obj->isTranslationAttribute($options['sort'])) {
                $query->orderByTranslation($options['sort']);
            } else {
                $query->orderBy($options['sort']);
            }
        }

        if (null !== $callback) {
            $callback($query);
        }

        $result = $query->get()
            ->pluck($options['name_key'], $options['value_key']);

        if ($withNotSet) {
            $result->prepend(__('cms-core::admin.layout.Not set'), '');
        }

        return $result->toArray();
    }
}
