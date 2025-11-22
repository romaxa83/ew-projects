<?php

namespace App\Dto\Inventories;

class InventoryFeatureDto
{
    public string|int $featureId;
    public array $valueIds = [];

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->featureId = data_get($data, 'feature_id');
        $self->valueIds = isset($data['value_id'])
            ? [$data['value_id']]
            : data_get($data, 'value_ids');

        return $self;
    }
}
