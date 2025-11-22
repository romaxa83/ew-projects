<?php

namespace App\Dto\Delivery;

use App\Models\Inventories\Inventory;

class DeliveryAddressRateDto
{
    public string|null $address;
    public string|null $city;
    public string|null $state;
    public string $zip;
    public array $inventories = [];

    public static function byArgs(array $data): static
    {
        $self = new static();

        $self->address = data_get($data, 'address');
        $self->city = data_get($data, 'city');
        $self->state = data_get($data, 'state');
        $self->zip = data_get($data, 'zip');

        $items = data_get($data, 'inventories', []);
        $ids = array_map(function ($item) { return (int) $item['id']; }, $items);

        $inventories = Inventory::query()->whereIn('id', $ids)->get()->keyBy('id');
        foreach ($items as $item) {
            $self->setInventory($inventories[$item['id']], (int) $item['count']);
        }
        return $self;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function setInventory(Inventory $inventory, int $count = 1): void
    {
        $this->inventories[] = [
            'inventory' => $inventory,
            'count' => $count,
        ];
    }

    public function getInventories(): array
    {
        return $this->inventories;
    }
}
