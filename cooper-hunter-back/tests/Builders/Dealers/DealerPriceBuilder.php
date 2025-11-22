<?php

namespace Tests\Builders\Dealers;

use App\Models\Catalog\Products\Product;
use App\Models\Dealers\Dealer;
use App\Models\Dealers\DealerPrice;
use Tests\Builders\BaseBuilder;

class DealerPriceBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return DealerPrice::class;
    }

    public function setProduct(Product $model): self
    {
        $this->data['product_id'] = $model->id;
        return $this;
    }

    public function setDealer(Dealer $model): self
    {
        $this->data['dealer_id'] = $model->id;
        return $this;
    }
}
