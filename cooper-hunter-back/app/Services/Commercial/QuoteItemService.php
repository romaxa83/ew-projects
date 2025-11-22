<?php

namespace App\Services\Commercial;

use App\Dto\Commercial\CommercialQuoteItemDto;
use App\Models\Commercial\CommercialQuote;
use App\Models\Commercial\QuoteItem;

class QuoteItemService
{
    public function create(
        CommercialQuote $model,
        CommercialQuoteItemDto $dto
    ): QuoteItem
    {
        $m = new QuoteItem();
        $m->commercial_quote_id = $model->id;
        $m->product_id = $dto->getProductID();
        $m->name = $dto->getName();
        $m->qty = $dto->getQty();
        $m->price = $dto->getPrice();

        $m->save();

        return $m;
    }

    public function update(
        QuoteItem $model,
        CommercialQuoteItemDto $dto
    ): QuoteItem
    {
        $model->product_id = $dto->getProductID();
        $model->name = $dto->getName();
        $model->qty = $dto->getQty();
        $model->price = $dto->getPrice();

        $model->save();

        return $model;
    }

    public function delete(QuoteItem $model): bool
    {
        return $model->delete();
    }
}

