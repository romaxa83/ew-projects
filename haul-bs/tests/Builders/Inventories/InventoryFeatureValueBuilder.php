<?php

namespace Tests\Builders\Inventories;

use App\Models\Inventories\Features\Feature;
use App\Models\Inventories\Features\Value;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\InventoryFeature;
use Tests\Builders\BaseBuilder;

class InventoryFeatureValueBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return InventoryFeature::class;
    }

    public function feature(Feature $model): self
    {
        $this->data['feature_id'] = $model->id;
        return $this;
    }

    public function value(Value $model): self
    {
        $this->data['value_id'] = $model->id;
        return $this;
    }

    public function inventory(Inventory $model): self
    {
        $this->data['inventory_id'] = $model->id;
        return $this;
    }

    public function create()
    {
        $m = new InventoryFeature();
        $m->inventory_id = $this->data['inventory_id'];
        $m->feature_id = $this->data['feature_id'];
        $m->value_id = $this->data['value_id'];
        $m->save();

        return $m;
    }
}
