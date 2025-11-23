<?php

namespace Tests\Builders\Items;

use App\Models\Item;
use App\Models\Item\Group;
use Tests\Builders\BaseBuilder;

class ItemBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Item::class;
    }

    public function title(string $value): self
    {
        $this->data['title'] = $value;
        return $this;
    }

    public function group(Group $group): self
    {
        $this->data['group_id'] = $group->id;
        return $this;
    }

    public function weight(string $value): self
    {
        $this->data['weight'] = $value;
        return $this;
    }

    public function cuft(string $value): self
    {
        $this->data['cuft'] = $value;
        return $this;
    }

    public function price(float $value): self
    {
        $this->data['price'] = $value;
        return $this;
    }

    public function divisionIds(array $value): self
    {
        $this->data['division_ids'] = $value;
        return $this;
    }
}
