<?php

namespace App\Dto\Employees;

use App\ValueObjects\Email;

final class EmployeeDto
{
    public string $firstName;
    public string $lastName;
    public Email $email;
    public ?string $password;
    public int $departmentID;
    public ?int $sipID;
    public bool $sendEmail;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->firstName = data_get($args, 'first_name');
        $self->lastName = data_get($args, 'last_name');
        $self->email = new Email(data_get($args, 'email'));
        $self->password = data_get($args, 'password');
        $self->departmentID = data_get($args, 'department_id');
        $self->sipID = data_get($args, 'sip_id');
        $self->sendEmail = data_get($args, 'send_email', false);

        return $self;
    }
}
