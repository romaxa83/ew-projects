<?php


namespace App\Dto;


use App\ValueObjects\Phone;

class PhoneDto
{
    private Phone $phone;
    private bool $isDefault;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->phone = new Phone($args['phone']);
        $dto->isDefault = (bool)$args['is_default'];

        return $dto;
    }

    /**
     * @return Phone
     */
    public function getPhone(): Phone
    {
        return $this->phone;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }
}
