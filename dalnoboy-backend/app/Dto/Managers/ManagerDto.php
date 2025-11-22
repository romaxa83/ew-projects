<?php


namespace App\Dto\Managers;


use App\Traits\Dto\HasPhonesDto;

class ManagerDto
{
    use HasPhonesDto;

    private string $firstName;
    private string $lastName;
    private ?string $secondName;
    private int $regionId;
    private string $city;
    private string $hash;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->firstName = $args['first_name'];
        $dto->lastName = $args['last_name'];
        $dto->secondName = data_get($args, 'second_name');
        $dto->city = $args['city'];
        $dto->regionId = (int)$args['region_id'];
        $dto->hash = md5($dto->firstName . $dto->lastName . $dto->secondName);

        $dto->setPhones($args['phones']);

        return $dto;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return int
     */
    public function getRegionId(): int
    {
        return $this->regionId;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getSecondName(): ?string
    {
        return $this->secondName;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }
}
