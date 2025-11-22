<?php

namespace App\Dto\Admins;

use App\Traits\Dto\HasPhonesDto;
use App\ValueObjects\Email;

class AdminDto
{
    use HasPhonesDto;

    private Email $email;
    private string $firstName;
    private string $lastName;
    private ?string $secondName;
    private ?string $password;
    private string $lang;
    private int $roleId;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->firstName = $args['first_name'];
        $dto->lastName = $args['last_name'];
        $dto->secondName = data_get($args, 'second_name');
        $dto->email = new Email($args['email']);
        $dto->lang = $args['language'];
        $dto->roleId = $args['role_id'];
        $dto->password = data_get($args, 'password');

        $dto->setPhones($args['phones']);

        return $dto;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
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
}
