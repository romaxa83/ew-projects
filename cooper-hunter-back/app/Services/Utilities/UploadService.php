<?php

namespace App\Services\Utilities;

use App\Contracts\Media\HasMedia;
use App\Dto\Utilities\Upload\UploadMultiLangDto;
use App\Exceptions\Utilities\Upload\UnsupportedModelTypeException;
use App\Models\BaseModel;
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;

class UploadService
{

    public function upload(UploadMultiLangDto $dto): bool
    {
        $model = Relation::getMorphedModel($dto->getModelType());

        if (!$model) {
            throw new UnsupportedModelTypeException($dto->getModelType());
        }

        /** @var BaseModel|HasMedia $model */
        $model = $model::query()
            ->findOrFail($dto->getModelId());

        try {
            foreach ($dto->getFiles() as $fileDto) {
                $model->addMedia($fileDto->getFile())
                    ->toMediaCollection(
                        $model->getMultiLangMediaCollectionName(
                            $fileDto->getLanguage()
                        )
                    );
            }
        } catch (Exception) {
            return false;
        }
        return true;
    }
}
