<?php

namespace Tests\Builders\Commercial;

use App\Models\Catalog\Products\Product;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialProjectUnit;
use Tests\Builders\BaseBuilder;

class ProjectUnitBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return CommercialProjectUnit::class;
    }

    public function setSerialNumber($value): self
    {
        $this->data['serial_number'] = $value;

        return $this;
    }

    public function setProduct(Product $model): self
    {
        $this->data['product_id'] = $model->id;

        return $this;
    }

    public function setProject(CommercialProject $model): self
    {
        $this->data['commercial_project_id'] = $model->id;

        return $this;
    }
}
