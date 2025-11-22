<?php

namespace App\Dto\Users;

use App\Traits\Dto\HasPhonesDto;
use App\ValueObjects\Email;

class UserDto
{
    use HasPhonesDto;

    private Email $email;
    private string $firstName;
    private string $lastName;
    private ?string $secondName;
    private string $lang;
    private int $roleId;
    private ?int $branchId;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->firstName = $args['first_name'];
        $dto->lastName = $args['last_name'];
        $dto->secondName = data_get($args, 'second_name');
        $dto->email = new Email($args['email']);
        $dto->lang = $args['language'];
        $dto->roleId = $args['role_id'];
        $dto->branchId = data_get($args, 'branch_id');

        $dto->setPhones($args['phones']);

        return $dto;
    }

    /**
     * @return Email
     */
    public function getEmail(): Email
    {
        return $this->email;
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
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @return int
     */
    public function getRoleId(): int
    {
        return $this->roleId;
    }

    /**
     * @return int
     */
    public function getBranchId(): ?int
    {
        return $this->branchId;
    }
}
