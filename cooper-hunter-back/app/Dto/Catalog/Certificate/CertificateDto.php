<?php

namespace App\Dto\Catalog\Certificate;

use App\Traits\AssertData;

class CertificateDto
{
    use AssertData;

    private string $number;
    private null|string $link;
    private int $typeId;

    public static function byArgs(array $args): self
    {
        static::assetField($args, 'number');
        static::assetField($args, 'type_id');

        $self = new self();

        $self->number = $args['number'];
        $self->link = $args['link'] ?? null;
        $self->typeId = $args['type_id'];

        return $self;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getLink(): null|string
    {
        return $this->link;
    }

    public function getTypeId(): int
    {
        return $this->typeId;
    }
}


