<?php

namespace App\Services\Orders\Parts;

use App\Dto\Orders\Parts\OrderDto;
use App\Dto\Orders\Parts\ShippingMethodDto;
use App\Enums\Orders\Parts\ShippingMethod;
use App\Models\Orders\Parts\Order;
use App\Models\Orders\Parts\Shipping;

class ShippingService
{
    public function __construct()
    {}

    public function handlerShippingForOrder(Order $model, OrderDto $dto,): Order
    {
        $notDelete = [];
        foreach ($dto->shippingMethods as $method){
            /** @var $method ShippingMethodDto */
            /** @var $shipping Shipping */
            // todo проверить кейс когда при обновлении shipping у него изменились айтемы
            if(
                $shipping = $model->shippingMethods()->where('method', $method->name)->first()
            ){
                $shipping = $this->update($shipping, $method);
            } else {
                $shipping = $this->create($method, $model);
                $model->items()->whereIn('id', $method->itemsIds)
                    ->update(['shipping_id' => $shipping->id]);
            }
            $notDelete[] = $shipping->id;
        }
        $model->shippingMethods()->whereNotIn('id', $notDelete)->delete();

        return $model->refresh();
    }

    public function create(ShippingMethodDto $dto, Order $order): Shipping
    {
        $model = new Shipping();
        $model->order_id = $order->id;
        $model->method = $dto->name;
        $model->cost = $dto->cost;
        $model->terms = $dto->terms;

        $model->save();

        return $model;
    }

    public function createDummy(Order $order): Shipping
    {
        return $this->create(ShippingMethodDto::byArgs([
            'name' => ShippingMethod::UPS_Standard(),
            'cost' => 0,
            'items_ids' => []
        ]), $order);
    }

    public function update(
        Shipping $model,
        ShippingMethodDto $dto,
    ): Shipping
    {
        $model->cost = $dto->cost;
        $model->terms = $dto->terms;

        $model->save();

        return $model;
    }
}
