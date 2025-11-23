<?php

namespace WezomCms\Orders\Presenters;

/**
 * Class NovaPoshtaApiPresenter
 *
 * @package WezomCms\Orders\Presenters
 */
class NovaPoshtaApiPresenter
{
    /**
     * @param  array  $data
     * @return array
     */
    public static function presentCities(array $data): array
    {
        return static::presentData($data);
    }

    /**
     * @param  array  $data
     * @return array
     */
    public static function presentWarehouses(array $data): array
    {
        return static::presentData($data);
    }

    /**
     * @param  array  $data
     * @return array
     */
    protected static function presentData(array $data): array
    {
        return tap([], function (&$result) use ($data) {
            $descriptionKey = app()->getLocale() === 'ru' ? 'DescriptionRu' : 'Description';
            foreach ($data as $datum) {
                $result[] = ['id' => $datum->Ref, 'text' => $datum->{$descriptionKey}];
            }
        });
    }
}
