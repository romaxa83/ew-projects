<?php

namespace App\Dto\Inventories;

class FeatureValueDto
{
    public string $name;
    public string $slug;
    public string $position;
    public bool $active;
    public string|int|null $featureId;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->name = data_get($data, 'name');
        $self->slug = data_get($data, 'slug');
        $self->position = data_get($data, 'position', 0);
        $self->featureId = data_get($data, 'feature_id');
        $self->active = $data['active'] ?? true;

        return $self;
    }
}
