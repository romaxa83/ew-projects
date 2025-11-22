<?php

namespace App\Dto\Projects;

class ProjectSystemUnitDto
{
    private int $productId;
    private string $serialNumber;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->productId = $args['product_id'];
        $self->serialNumber = $args['serial_number'];

        return $self;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }
}
