<?php

namespace WezomCms\Catalog\Models;

use Cookie;
use Illuminate\Support\Collection;

class ViewedProducts
{
    protected const MAX_ITEMS = 30;
    protected const STORAGE_KEY = 'viewed-products';

    /**
     * @param  int  $productId
     */
    public static function add(int $productId)
    {
        $items = static::getIds();

        $items = array_prepend($items, $productId);

        $items = array_unique($items);

        $items = array_slice($items, 0, static::MAX_ITEMS);

        Cookie::queue(Cookie::make(static::STORAGE_KEY, base64_encode(json_encode($items))));
    }

    /**
     * @return array
     */
    public static function getIds(): array
    {
        $data = Cookie::get(static::STORAGE_KEY);

        if (!$data) {
            return [];
        }

        return (array)json_decode(base64_decode($data), true);
    }

    /**
     * @param  int|null  $limit
     * @return Collection
     */
    public static function getProducts(?int $limit = null): Collection
    {
        $ids = static::getIds();

        if (!count($ids)) {
            return collect();
        }

        $result = Product::published()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(function ($model) use ($ids) {
                return array_search($model->getKey(), $ids);
            });

        return $limit !== null ? $result->take($limit) : $result;
    }

    /**
     * Delete all viewed products from storage.
     */
    public static function deleteAll()
    {
        Cookie::queue(Cookie::forget(static::STORAGE_KEY));
    }
}
