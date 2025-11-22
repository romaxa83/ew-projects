<?php

namespace App\Dto\Users;

use App\Foundations\ValueObjects\Email;
use App\Foundations\ValueObjects\Phone;
use App\Models\Users\User;
use Carbon\CarbonImmutable;

class UserSyncDto
{
    public string|int $id;
    public string $firstName;
    public string $lastName;
    public string|null $secondName;
    public Email $email;
    public Phone|null $phone;
    public string|null $phoneExtension;
    public array $phones;
    public string $status;
    public string $lang;
    public string|null $password;
    public CarbonImmutable|null $createdAt;
    public CarbonImmutable|null $updatedAt;
    public CarbonImmutable|null $deletedAt;
    public int $roleId;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $email = data_get($data, 'email');
        if(is_numeric($email)){
            $email .= User::PREFIX_DELETE_EMAIL;
        }

        $self->id = data_get($data, 'id');
        $self->firstName = data_get($data, 'first_name');
        $self->lastName = data_get($data, 'last_name');
        $self->secondName = data_get($data, 'second_name');
        $self->email = new Email($email);
        $self->phone = data_get($data, 'phone')
            ? new Phone(data_get($data, 'phone'))
            : null
        ;
        $self->phoneExtension = data_get($data, 'phone_extension');
        $self->phones = data_get($data, 'phones')
            ? data_get($data, 'phones')
            : []
        ;
        $self->lang = data_get($data, 'language');
        $self->status = data_get($data, 'status');
        $self->password = data_get($data, 'password');
        $self->createdAt = data_get($data, 'created_at')
            ? CarbonImmutable::createFromTimestamp(data_get($data, 'created_at'))
            : null
        ;
        $self->updatedAt = data_get($data, 'updated_at')
            ? CarbonImmutable::createFromTimestamp(data_get($data, 'updated_at'))
            : null
        ;
        $self->deletedAt = data_get($data, 'deleted_at')
            ? CarbonImmutable::createFromTimestamp(data_get($data, 'deleted_at'))
            : null
        ;

        $self->roleId = data_get($data, 'role_id');

        return $self;
    }
}
