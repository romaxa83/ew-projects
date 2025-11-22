<?php

namespace App\Dto\Catalog;

class SerialNumberDto
{
    private array $serialNumbers;
    private string $productGuid;

    public static function byArgs(array $args): static
    {
        $instance = new static();

        foreach ($args['serial_numbers'] as $key => $number){
            $instance->serialNumbers[$key] = strtoupper($number);
        }
        $instance->productGuid = $args['product_guid'];

        return $instance;
    }

    public function getSerialNumbers(): array
    {
        return $this->serialNumbers;
    }

    public function getProductGuid(): string
    {
        return $this->productGuid;
    }
}
