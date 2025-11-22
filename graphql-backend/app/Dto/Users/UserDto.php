<?php

namespace App\Dto\Users;

use App\ValueObjects\Email;
use App\ValueObjects\Phone;

class UserDto
{

    private Email $email;

    private ?Phone $phone;

    private string $firstName;

    private string $lastName;

    private string $middleName;

    private ?string $password;

    private ?string $lang;

    private ?int $roleId;

    public static function byArgs(array $args): static
    {
        $self = new static();

        $self->firstName = $args['first_name'];
        $self->lastName = $args['last_name'];
        $self->middleName = $args['middle_name'];
        $self->email = new Email($args['email']);
        $self->phone = isset($args['phone']) ? new Phone($args['phone']) : null;
        $self->password = $args['password'] ?? null;
        $self->lang = $args['lang'] ?? null;
        $self->roleId = $args['role_id'] ?? null;

        return $self;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function hasPassword(): bool
    {
        return (bool)$this->password;
    }

    public function hasLang(): bool
    {
        return (bool)$this->lang;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    public function hasRoleId(): bool
    {
        return (bool)$this->roleId;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }
}
