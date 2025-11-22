<?php

namespace App\Dto\BodyShop\TypesOfWork;

class TypeOfWorkDto
{
    protected array $typeOfWorkData;

    protected array $inventoriesData;

    public static function byParams(array $data): self
    {
        $dto = new self();

        $dto->fillData($data);

        return $dto;
    }

    public function fillData(array $data): void
    {
        $this->typeOfWorkData = [
            'name' => $data['name'],
            'hourly_rate' => $data['hourly_rate'],
            'duration' => $data['duration'],
        ];

        if (isset($data['id'])) {
            $this->typeOfWorkData['id'] = $data['id'];
        }

        $this->inventoriesData = $data['inventories'] ?? [];
    }

    public function getTypeOfWorkData(): array
    {
        return $this->typeOfWorkData;
    }

    public function getInventoriesData(): array
    {
        return $this->inventoriesData;
    }
}
