<?php

namespace App\Dto\Admins;

use App\ValueObjects\Email;
use Core\Dto\BaseGraphQLDto;
use Core\GraphQL\Helpers\GraphQLTypeName;

#[GraphQLTypeName('AdminInput')]
class AdminDto extends BaseGraphQLDto
{
    private string $name;

    private string $email;

    private null|string $password;

    private null|int $role_id;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];
        $self->email = $args['email'];
        $self->password = $args['password'] ?? null;
        $self->role_id = $args['role_id'] ?? null;

        return $self;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
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
        return (bool)$this->role_id;
    }

    public function getRoleId(): ?int
    {
        return $this->role_id;
    }
}
