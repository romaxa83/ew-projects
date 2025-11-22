<?php

namespace App\Dto\Customers;

use App\Enums\Customers\CustomerType;
use App\Foundations\ValueObjects\Email;
use App\Foundations\ValueObjects\Phone;
use App\Models\Customers\Customer;

class CustomerDto
{
    public string $firstName;
    public string $lastName;
    public Phone|null $phone;
    public string $type;
    public string|null $phoneExtension;
    public array $phones;
    public Email $email;
    public string|null $notes;
    public string|int|null $originId;
    public string|int|null $salesManagedId = null;
    public bool $fromHaulk;
    public array $tags = [];
    public array $files = [];


    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->firstName = data_get($data, 'first_name');
        $self->lastName = data_get($data, 'last_name');
        $self->phone = data_get($data, 'phone')
            ? new Phone(data_get($data, 'phone'))
            : null;
        $self->phoneExtension = data_get($data, 'phone_extension');
        $self->phones = $data['phones'] ?? [];
        $self->email = new Email(data_get($data, 'email'));
        $self->notes = data_get($data, 'notes');
        $self->originId = data_get($data, 'origin_id');
        $self->fromHaulk = data_get($data, 'from_haulk', false);
        $self->tags = data_get($data, 'tags', []);
        $self->files = data_get($data, Customer::ATTACHMENT_FIELD_NAME, []);
        $self->type = $data['type'] ?? CustomerType::BS();

        if(isset($data['sales_manager_id'])){
            $self->salesManagedId = $data['sales_manager_id'];
        } else {
            if(auth_user()?->role->isSalesManager()){
                $self->salesManagedId = auth_user()->id;
            }
        }

        return $self;
    }
}
