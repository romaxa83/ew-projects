<?php

namespace App\Dto\TypeOfWorks;

class TypeOfWorkDto
{
    public string $name;
    public string $duration;
    public float $hourlyRate;

    public array $inventories = [];

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->name = data_get($data, 'name');
        $self->duration = data_get($data, 'duration');
        $self->hourlyRate = data_get($data, 'hourly_rate');

        foreach ($data['inventories'] ?? [] as $item){
            $self->inventories[] = TypeOfWorkInventoryDto::byArgs($item);
        }

        return $self;
    }
}
