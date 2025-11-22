<?php

namespace Tests\Builders\Company;

use App\Models\Catalog\Products\Product;
use App\Models\Companies\Company;
use App\Models\Companies\Price;
use Tests\Builders\BaseBuilder;

class CompanyPriceBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Price::class;
    }

    public function setProduct(Product $model): self
    {
        $this->data['product_id'] = $model->id;
        return $this;
    }

    public function setCompany(Company $model): self
    {
        $this->data['company_id'] = $model->id;
        return $this;
    }

    public function setDesc($value): self
    {
        $this->data['desc'] = $value;
        return $this;
    }
}

