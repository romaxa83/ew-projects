<?php

namespace App\Services\Catalog\Manuals;

use App\Dto\Catalog\Manuals\ManualDto;
use App\Dto\Catalog\Manuals\ManualListDto;
use App\Models\Catalog\Manuals\Manual;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class ManualService
{
    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function createMany(ManualListDto $dto): Collection
    {
        $collection = collect();

        foreach ($dto->getManualsDto() as $manualDto) {
            $collection->push(
                $this->create($manualDto)
            );
        }

        return $collection;
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function create(ManualDto $dto): Manual
    {
        $manual = new Manual();
        $manual->manual_group_id = $dto->getManualGroupId();
        $manual->save();

        $this->storePdf($manual, $dto);

        return $manual;
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    protected function storePdf(Manual $manual, ManualDto $dto): void
    {
        $manual
            ->addMedia($dto->getPdf())
            ->toMediaCollection(Manual::MEDIA_COLLECTION_NAME);
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function update(Manual $manual, ManualDto $dto): Manual
    {
        $manual->manual_group_id = $dto->getManualGroupId();
        $manual->save();

        $this->storePdf($manual, $dto);

        return $manual;
    }

    public function delete(Manual $manual): bool
    {
        return $manual->delete();
    }
}
