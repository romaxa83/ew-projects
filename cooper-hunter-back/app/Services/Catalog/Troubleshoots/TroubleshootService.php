<?php

namespace App\Services\Catalog\Troubleshoots;

use App\Dto\Catalog\Troubleshoots\TroubleshootDto;
use App\Models\Catalog\Troubleshoots\Troubleshoot;
use App\Traits\Model\ToggleActive;
use Exception;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;

class TroubleshootService
{
    use ToggleActive;

    /**
     * @param TroubleshootDto $dto
     * @return Troubleshoot
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function create(TroubleshootDto $dto): Troubleshoot
    {
        $model = new Troubleshoot();

        $this->fill($dto, $model);
        $this->storePdf($model, $dto);
        $model->save();
        return $model;
    }

    protected function fill(TroubleshootDto $dto, Troubleshoot $model): void
    {
        $model->active = $dto->getActive();
        $model->name = $dto->getName();
        $model->group_id = $dto->getGroupId();
    }

    /**
     * @param Troubleshoot $model
     * @param TroubleshootDto $dto
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    protected function storePdf(Troubleshoot $model, TroubleshootDto $dto): void
    {
        if ($dto->getPdf()) {
            $model->addMedia($dto->getPdf())
                ->toMediaCollection(Troubleshoot::MEDIA_COLLECTION_NAME);
        }
    }

    /**
     * @param TroubleshootDto $dto
     * @param Troubleshoot $model
     * @return Troubleshoot
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(TroubleshootDto $dto, Troubleshoot $model): Troubleshoot
    {
        $this->fill($dto, $model);
        $this->storePdf($model, $dto);
        $model->save();

        $model->refresh();

        return $model;
    }

    /**
     * @param Troubleshoot $model
     * @throws Exception
     */
    public function remove(Troubleshoot $model): void
    {
        try {
            $model->forceDelete();
        } catch (Throwable $e) {
            logger($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}


