<?php

namespace App\Dto\Catalog\Products;

class ProductCertificateDto
{
    private string $type;
    private string $number;
    private ?string $link;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->type = $args['type_name'];
        $self->number = $args['number'];
        $self->link = $args['link'] ?? null;

        return $self;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }
}
