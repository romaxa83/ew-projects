<?php

namespace App\Dto\Admins;

use App\ValueObjects\Email;

class AdminDto
{
    private string $name;

    private Email $email;

    private null|string $password;

    private null|int $roleId;
    public array $relationAdmins = [];

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];
        $self->email = new Email($args['email']);
        $self->password = $args['password'] ?? null;
        $self->roleId = $args['role_id'] ?? null;
        $self->relationAdmins = data_get($args, 'relations', []);

        return $self;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function hasPassword(): bool
    {
        return (bool)$this->password;
    }

    public function hasRoleId(): bool
    {
        return (bool)$this->roleId;
    }

    public function getRoleId(): ?int
    {
        return $this->roleId;
    }
}
