<?php

namespace App\Foundations\Modules\Media\Dto;

class MediaSyncDto
{
    public int $id;
    public string $modelType;
    public int $modelId;
    public string $collectionName;
    public string $fileName;
    public string $name;
    public string $mimeType;
    public string $disk;
    public string $conversionsDisk;
    public int $size;
    public array $manipulations;
    public array $customProperties;
    public array $responsiveImages;
    public array $generatedConversions;
    public int $orderColumn;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $conversion = $data['custom_properties']['generated_conversions'] ?? [];

        $self->id = data_get($data, 'id');
        $self->modelType = data_get($data, 'model_type');
        $self->modelId = data_get($data, 'model_id');
        $self->collectionName = data_get($data, 'collection_name');
        $self->fileName = data_get($data, 'file_name');
        $self->name = data_get($data, 'name');
        $self->mimeType = data_get($data, 'mime_type');
        $self->disk = data_get($data, 'disk');
        $self->conversionsDisk = data_get($data, 'disk');
        $self->size = data_get($data, 'size');
        $self->manipulations = data_get($data, 'manipulations');
        $self->customProperties = data_get($data, 'custom_properties');
        $self->responsiveImages = data_get($data, 'responsive_images');
        $self->generatedConversions = $conversion;
        $self->orderColumn = data_get($data, 'order_column');

        return $self;
    }
}

