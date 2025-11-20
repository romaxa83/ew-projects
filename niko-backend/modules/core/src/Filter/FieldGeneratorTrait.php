<?php

namespace WezomCms\Core\Filter;

use WezomCms\Core\Contracts\Filter\FilterFieldInterface;

trait FieldGeneratorTrait
{
    /**
     * @param  array  $params
     * @return static
     */
    public static function id(array $params = [])
    {
        $params['name'] = 'id';

        static::addParameter($params, 'size', 1);

        static::addParameter($params, 'label', __('cms-core::admin.filter.ID'));

        return static::make($params);
    }

    /**
     * @param  array  $params
     * @return static
     */
    public static function makeName(array $params = [])
    {
        static::addParameter($params, 'name', 'name');

        static::addParameter($params, 'label', __('cms-core::admin.filter.Name'));

        return static::make($params);
    }

    /**
     * @param  array  $params
     * @return static
     */
    public static function published(array $params = [])
    {
        $params['type'] = FilterFieldInterface::TYPE_SELECT;

        static::addParameter($params, 'size', 2);

        static::addParameter(
            $params,
            'options',
            [
                0 => __('cms-core::admin.filter.Unpublished'),
                1 => __('cms-core::admin.filter.Published'),
            ]
        );

        static::addParameter($params, 'name', 'published');

        static::addParameter($params, 'label', __('cms-core::admin.filter.Publication'));

        return static::make($params);
    }

    /**
     * @param  array  $params
     * @return static
     */
    public static function read(array $params = [])
    {
        $params['type'] = FilterFieldInterface::TYPE_SELECT;

        static::addParameter($params, 'size', 2);

        static::addParameter($params, 'name', 'read');

        static::addParameter($params, 'label', __('cms-core::admin.layout.Read'));

        static::addParameter(
            $params,
            'options',
            [
                1 => __('cms-core::admin.layout.Yes'),
                0 => __('cms-core::admin.layout.No'),
            ]
        );

        return static::make($params);
    }

    /**
     * @param  array  $params
     * @return static
     */
    public static function active(array $params = [])
    {
        $params['type'] = FilterFieldInterface::TYPE_SELECT;

        static::addParameter($params, 'size', 2);

        static::addParameter($params, 'name', 'active');

        static::addParameter($params, 'label', __('cms-core::admin.layout.Status'));

        static::addParameter(
            $params,
            'options',
            [
                0 => __('cms-core::admin.layout.Inactive'),
                1 => __('cms-core::admin.layout.Active'),
            ]
        );

        return static::make($params);
    }

    /**
     * @param  array  $params
     * @return static
     */
    public static function locale(array $params = [])
    {
        $params['type'] = 'select';

        static::addParameter($params, 'name', 'locale');

        static::addParameter($params, 'size', 2);

        static::addParameter($params, 'options', app('locales'));

        static::addParameter($params, 'label', __('cms-core::admin.filter.Language'));

        return static::make($params);
    }

    /**
     * @param  array  $params
     * @param $name
     * @param $value
     */
    protected static function addParameter(array &$params, $name, $value)
    {
        if (!isset($params[$name])) {
            $params[$name] = $value;
        }
    }
}
