<?php

namespace App\Services\Orders\Dealer;

use App\Dto\Orders\Dealer\OrderItemDto;
use App\Models\Catalog\Products\Product;
use App\Models\Companies\Price;
use App\Models\Orders\Dealer\Item;
use App\Models\Orders\Dealer\Order;
use Core\Exceptions\TranslatedException;
use Illuminate\Database\Eloquent\Collection;

class OrderItemService
{
    public function __construct()
    {}

    public function add(Order $order, Price $price, int $qty = 1): Item
    {
        if(array_key_exists(
            $price->product_id,
            $order->items->pluck('product_id', 'product_id')->toArray()
        )){
            throw new TranslatedException(__("exceptions.dealer.order.item is already on order"), 502);
        }

        $model = new Item();
        $model->order_id = $order->id;
        $model->product_id = $price->product_id;
        $model->price = $price->price;
        $model->qty = $qty;

        $model->save();

        return $model;
    }

    public function create(Order $order, OrderItemDto $dto, Product $product): Item
    {
        $model = new Item();
        $model->order_id = $order->id;
        $model->product_id = $product->id;
        $model->price = $dto->price;
        $model->qty = $dto->qty;
        $model->discount = $dto->discount;
        $model->discount_total = $dto->discount_total;
        $model->total = $dto->total;
        $model->description = $dto->description;

        $model->save();

        return $model;
    }

    public function update(Item $model, int $qty): Item
    {
        $model->qty = $qty;

        $model->save();

        return $model;
    }

    public function copies(Order $order, Collection $items): void
    {
        foreach ($items as $item){
            /** @var $item Item */
            $this->copy($order, $item);
        }
    }

    public function copy(Order $order, Item $model): Item
    {
        $copy = new Item();
        $copy->order_id = $order->id;
        $copy->product_id = $model->product_id;
        $copy->qty = $model->qty;
        $copy->price = $model->price;
        $copy->discount = $model->discount;

        $copy->save();

        return $copy;
    }

    public function delete(Item $model): bool
    {
        return $model->forceDelete();
    }
}
