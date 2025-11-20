<?php

namespace App\Dto\Admins;

use App\ValueObjects\Email;

class AdminProfileDto
{
    public ?string $name;

    public  ?Email $email;

    public  null|string $password;

    public  null|bool $notify;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'] ?? null;
        $self->email = isset($args['email']) ? new Email($args['email']) : null;
        $self->password = $args['password'] ?? null;
        $self->notify = $args['notify'] ?? null;

        return $self;
    }
}
