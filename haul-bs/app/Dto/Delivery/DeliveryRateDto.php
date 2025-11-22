<?php

namespace App\Dto\Delivery;

use Carbon\Carbon;

class DeliveryRateDto
{
    public string $id;
    public string $name;
    public float $amount;
    public Carbon $date;
    public ?string $text_additional;

    public static function byArgs(array $data): static
    {
        $self = new static();

        $self->id = data_get($data, 'id');
        $self->name = data_get($data, 'name');
        $self->amount = (float) data_get($data, 'amount');
        $self->date = data_get($data, 'date');
        $self->text_additional = data_get($data, 'text_additional');

        return $self;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function getAmount(): float
    {
        return $this->amount;
    }
    public function getDate(): Carbon
    {
        return $this->date;
    }
    public function getTextAdditional(): ?string
    {
        return $this->text_additional;
    }
}
