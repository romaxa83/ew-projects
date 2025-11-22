<?php

namespace App\Dto\BodyShop\Inventories;

class InventoryDto
{
    private array $inventoryData;

    private ?PurchaseDto $purchaseData;

    public static function byParams(array $data): self
    {
        $dto = new self();

        $dto->inventoryData = [
            'name' => $data['name'],
            'stock_number' => $data['stock_number'],
            'price_retail' => $data['price_retail'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'supplier_id' => $data['supplier_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'unit_id' => $data['unit_id'],
            'min_limit' => $data['min_limit'] ?? null,
            'for_sale' => $data['for_sale'] ?? false,
            'length' => $data['length'] ?? null,
            'width' => $data['width'] ?? null,
            'height' => $data['height'] ?? null,
            'weight' => $data['weight'] ?? null,
            'min_limit_price' => $data['min_limit_price'] ?? null,
        ];

        if ($data['purchase'] ?? null) {
            $dto->purchaseData = PurchaseDto::byParams($data['purchase']);
        }

        return $dto;
    }

    public function getInventoryData(): array
    {
        return $this->inventoryData;
    }

    public function getPurchaseData(): ?PurchaseDto
    {
        return $this->purchaseData;
    }
}
