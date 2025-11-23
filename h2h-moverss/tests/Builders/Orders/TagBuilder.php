<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use Tests\Builders\BaseBuilder;

class TagBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order\Tag::class;
    }

    public function id(int $value): self
    {
        $this->data['id'] = $value;
        return $this;
    }

    public function asBadZip(): self
    {
        return $this->id(Order\Tag::BAD_ZIP_ID);
    }

    public function asCantService(): self
    {
        return $this->id(Order\Tag::CANT_SERVICE_ID);
    }

    public function asNoAnswer(): self
    {
        return $this->id(Order\Tag::NO_ANSWER);
    }
}
