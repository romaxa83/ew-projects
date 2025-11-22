<?php

namespace App\Dto\Saas\Admins;

class AdminDto
{
    private string $fullName;

    private string $email;

    private ?string $phone;

    private ?int $role_id;

    private ?string $password;

    private function __construct()
    {
    }

    public static function byParams(array $params): self
    {
        $self = new self();

        $self->fullName = $params['full_name'];
        $self->email = $params['email'];
        $self->phone = $params['phone'];
        $self->password = $params['password'] ?? null;
        $self->role_id = $params['role_id'] ?? null;

        return $self;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getRoleId(): ?int
    {
        return $this->role_id;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
