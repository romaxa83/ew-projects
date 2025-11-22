<?php

namespace App\Dto\Security;

class IpAccessDto
{
    private string $address;
    private ?string $description;
    private bool $active;

    private function __construct()
    {
    }

    public static function build(array $args): static
    {
        $self = new static();

        $self->address = $args['address'];
        $self->description = $args['description'] ?? null;
        $self->active = (boolean)$args['active'];

        return $self;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getAddress(): string
    {
        return $this->address;
    }
}
