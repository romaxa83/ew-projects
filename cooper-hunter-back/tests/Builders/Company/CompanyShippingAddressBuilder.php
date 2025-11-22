<?php

namespace Tests\Builders\Company;

use App\Models\Companies\Company;
use App\Models\Companies\ShippingAddress;
use Tests\Builders\BaseBuilder;

class CompanyShippingAddressBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return ShippingAddress::class;
    }

    public function setCompany(Company $model): self
    {
        $this->data['company_id'] = $model->id;
        return $this;
    }

    public function setName(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }
}

