<?php

namespace App\Foundations\Modules\Media\Services;

use App\Foundations\Modules\Media\Dto\MediaSyncDto;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    public function createFromSync(MediaSyncDto $dto): Media
    {
        $model = new Media();
        $model->origin_id = $dto->id;
        $model->model_type = $dto->modelType;
        $model->model_id = $dto->modelId;
        $model->collection_name = $dto->collectionName;
        $model->name = $dto->name;
        $model->file_name = $dto->fileName;
        $model->mime_type = $dto->mimeType;
        $model->disk = $dto->disk;
        $model->conversions_disk = $dto->conversionsDisk;
        $model->size = $dto->size;
        $model->manipulations = $dto->manipulations;
        $model->custom_properties = $dto->customProperties;
        $model->responsive_images = $dto->responsiveImages;
        $model->generated_conversions = $dto->generatedConversions;
        $model->order_column = $dto->orderColumn;

        $model->save();

        return $model;
    }
}
