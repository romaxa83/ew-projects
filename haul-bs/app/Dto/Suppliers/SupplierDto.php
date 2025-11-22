<?php

namespace App\Dto\Suppliers;

class SupplierDto
{
    public string $name;
    public string|null $url;

    /** @var array<int, SupplierContactDto> */
    public array $contacts;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->name = data_get($data, 'name');
        $self->url = data_get($data, 'url');

        foreach ($data['contacts'] ??  [] as $item) {
            $self->contacts[] = SupplierContactDto::byArgs($item);
        }

        return $self;
    }
}

