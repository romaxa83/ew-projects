<?php

declare(strict_types=1);

namespace App\Services\Stores;

use App\Dto\Stores\Stores\StoreDto;
use App\Models\Stores\Store;

class StoreService
{
    public function create(StoreDto $dto): Store
    {
        return $this->store(new Store(), $dto);
    }

    protected function store(Store $store, StoreDto $dto): Store
    {
        $this->fill($store, $dto);

        $store->save();

        $this->saveTranslations($store, $dto);

        return $store;
    }

    protected function fill(Store $store, StoreDto $dto): void
    {
        $store->category()->associate($dto->getStoreCategoryId());
        $store->active = $dto->getActive();
        $store->link = $dto->getLink();
    }

    protected function saveTranslations(Store $store, StoreDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $store->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'title' => $translation->getTitle(),
                ]
            );
        }
    }

    public function update(Store $store, StoreDto $dto): Store
    {
        return $this->store($store, $dto);
    }

    public function delete(Store $store): bool
    {
        return $store->delete();
    }
}
