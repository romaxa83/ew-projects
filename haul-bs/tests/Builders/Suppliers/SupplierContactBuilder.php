<?php

namespace Tests\Builders\Suppliers;

use App\Foundations\ValueObjects\Email;
use App\Models\Suppliers\Supplier;
use App\Models\Suppliers\SupplierContact;
use Tests\Builders\BaseBuilder;

class SupplierContactBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return SupplierContact::class;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }

    public function email(string $value): self
    {
        $this->data['email'] = new Email($value);
        return $this;
    }

    public function supplier(Supplier $model): self
    {
        $this->data['supplier_id'] = $model->id;
        return $this;
    }

    public function main(bool $value): self
    {
        $this->data['is_main'] = $value;
        return $this;
    }
}
