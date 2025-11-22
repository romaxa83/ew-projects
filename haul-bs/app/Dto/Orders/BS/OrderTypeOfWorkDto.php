<?php

namespace App\Dto\Orders\BS;

use App\Dto\TypeOfWorks\TypeOfWorkDto;

class OrderTypeOfWorkDto extends TypeOfWorkDto
{
    public int|string|null $id;

    public bool $saveToTheList;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->id = $data['id'] ?? null;
        $self->name = $data['name'];
        $self->duration = $data['duration'];
        $self->hourlyRate = $data['hourly_rate'];
        $self->saveToTheList = $data['save_to_the_list'] ?? false;

        foreach ($data['inventories'] ?? [] as $item){
            $self->inventories[] = OrderTypeOfWorkInventoryDto::byArgs($item);
        }

        return $self;
    }
}

