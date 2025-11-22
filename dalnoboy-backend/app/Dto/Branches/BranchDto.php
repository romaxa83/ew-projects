<?php


namespace App\Dto\Branches;


use App\Traits\Dto\HasPhonesDto;

class BranchDto
{
    use HasPhonesDto;

    private string $name;
    private string $city;
    private int $regionId;
    private string $address;
    private bool $active;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->name = $args['name'];
        $dto->city = $args['city'];
        $dto->regionId = (int)$args['region_id'];
        $dto->address = $args['address'];
        $dto->active = $args['active'];
        $dto->setPhones($args['phones']);

        return $dto;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getRegionId(): int
    {
        return $this->regionId;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
