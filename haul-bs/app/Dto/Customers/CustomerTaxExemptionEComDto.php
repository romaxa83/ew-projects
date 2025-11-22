<?php

namespace App\Dto\Customers;

class CustomerTaxExemptionEComDto
{
    public string $link;
    public string $file_name;


    public static function byArgs(array $data): self
    {
        $self = new self();
        $self->link = data_get($data, 'link');
        $self->file_name = data_get($data, 'file_name');

        return $self;
    }
}
