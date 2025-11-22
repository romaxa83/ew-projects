<?php

declare(strict_types=1);

namespace App\Services\Stores;

use App\Dto\Stores\StoreCategories\StoreCategoryDto;
use App\Models\Stores\StoreCategory;

class StoreCategoryService
{
    public function create(StoreCategoryDto $dto): StoreCategory
    {
        $storeCategory = new StoreCategory();

        return $this->store($storeCategory, $dto);
    }

    protected function store(StoreCategory $storeCategory, StoreCategoryDto $dto): StoreCategory
    {
        $this->fill($storeCategory, $dto);

        $storeCategory->save();

        $this->saveTranslations($storeCategory, $dto);

        return $storeCategory;
    }

    protected function fill(StoreCategory $storeCategory, StoreCategoryDto $dto): void
    {
        $storeCategory->active = $dto->getActive();
    }

    protected function saveTranslations(StoreCategory $storeCategory, StoreCategoryDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $storeCategory->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'title' => $translation->getTitle(),
                ]
            );
        }
    }

    public function update(StoreCategory $storeCategory, StoreCategoryDto $dto): StoreCategory
    {
        return $this->store($storeCategory, $dto);
    }

    public function delete(StoreCategory $storeCategory): bool
    {
        return $storeCategory->delete();
    }
}
