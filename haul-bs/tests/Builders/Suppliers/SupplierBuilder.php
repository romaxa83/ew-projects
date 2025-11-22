<?php

namespace Tests\Builders\Suppliers;

use App\Models\Suppliers\Supplier;
use Tests\Builders\BaseBuilder;

class SupplierBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Supplier::class;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }
}
