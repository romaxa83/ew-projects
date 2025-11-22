<?php

namespace App\Services\BodyShop\Inventories;

use App\Exceptions\HasRelatedEntitiesException;
use App\Models\BodyShop\Inventories\Category;
use DB;
use Exception;
use Log;

class CategoryService
{
    public function create(array $attributes): Category
    {
        try {
            DB::beginTransaction();

            /** @var Category $category */
            $category = Category::query()->make($attributes);
            $category->saveOrFail();

            DB::commit();

            return $category;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function update(Category $category, array $attributes): Category
    {
        try {
            DB::beginTransaction();

            $category->update($attributes);

            DB::commit();

            return $category;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function destroy(Category $category): Category
    {
        if ($category->hasRelatedEntities()) {
            throw new HasRelatedEntitiesException();
        }

        $category->delete();

        return $category;
    }
}
