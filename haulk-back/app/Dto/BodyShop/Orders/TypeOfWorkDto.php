<?php

namespace App\Dto\BodyShop\Orders;

class TypeOfWorkDto extends \App\Dto\BodyShop\TypesOfWork\TypeOfWorkDto
{
    private bool $saveToTheList;

    public static function byParams(array $data): self
    {
        $dto = new self();

        $dto->fillData($data);;

        return $dto;
    }

    public function fillData(array $data): void
    {
        parent::fillData($data);

        $this->saveToTheList = $data['save_to_the_list'] ?? false;
    }

    public function isSaveToTheList(): bool
    {
        return $this->saveToTheList;
    }
}
