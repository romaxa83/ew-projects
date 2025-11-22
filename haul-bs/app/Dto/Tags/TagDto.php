<?php

namespace App\Dto\Tags;

use App\Enums\Tags\TagType;

class TagDto
{
    public string $name;
    public string $color;
    public TagType $type;
    public string|int|null $originId;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->name = data_get($data, 'name');
        $self->color = data_get($data, 'color');
        $self->type = TagType::fromValue(data_get($data, 'type'));
        $self->originId = data_get($data, 'origin_id');

        return $self;
    }
}
