<?php

namespace App\Dto\Catalog\Certificate;

use App\Traits\AssertData;

class TypeDto
{
    use AssertData;

    private string $type;

    public static function byArgs(array $args): self
    {
        static::assetField($args, 'type');

        $self = new self();

        $self->type = $args['type'];

        return $self;
    }

    public function getType(): string
    {
        return $this->type;
    }
}



