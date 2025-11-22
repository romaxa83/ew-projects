<?php

namespace App\Dto\Inventories;

class FeatureDto
{
    public string $name;
    public string $slug;
    public bool $multiple;
    public bool $active;
    public int $position;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->name = data_get($data, 'name');
        $self->slug = data_get($data, 'slug');
        $self->multiple = $data['multiple'] ?? false;
        $self->active = $data['active'] ?? true;
        $self->position = data_get($data, 'position', 0);

        return $self;
    }
}
