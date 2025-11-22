<?php

namespace App\Dto\Customers;

use Illuminate\Http\UploadedFile;

class CustomerTaxExemptionDto
{
    public string $date_active_to;
    public UploadedFile $file;


    public static function byArgs(array $data): self
    {
        $self = new self();
        $self->date_active_to = data_get($data, 'date_active_to');
        $self->file = data_get($data, 'file');

        return $self;
    }
}
