<?php

namespace App\Dto\BodyShop\Suppliers;

use App\Models\Orders\Order;
use Illuminate\Http\UploadedFile;

class SupplierDto
{
    private array $supplierData;

    private array $supplierContactsData;

    public static function byParams(array $data): self
    {
        $dto = new self();

        $dto->supplierData = [
            'name' => $data['name'],
            'url' => $data['url'] ?? '',
        ];

        $dto->supplierContactsData = $data['contacts'] ?? [];

        return $dto;
    }

    public function getSupplierData(): array
    {
        return $this->supplierData;
    }

    public function getSupplierContactsData(): array
    {
        return $this->supplierContactsData;
    }
}
