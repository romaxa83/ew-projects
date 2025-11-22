<?php

namespace App\Http\Requests\Orders\BS;

use App\Models\Orders\BS\Order;
use App\Repositories\Orders\BS\OrderRepository;
use App\Rules\Orders\BS\HasEnoughInventoryForWholeOrder;
use Illuminate\Foundation\Http\FormRequest;


class OrderRestoreRequest extends FormRequest
{
    protected Order|null $order = null;

    public function rules(): array
    {
        $order = $this->getOrder();

        return [
            'order' => [new HasEnoughInventoryForWholeOrder($order)],
        ];
    }

    public function prepareForValidation()
    {
        $this->merge(['order' => $this->getOrder()]);
    }

    public function getOrder(): Order
    {
        if(!$this->order){
            /** @var $repo OrderRepository */
            $repo = resolve(OrderRepository::class);

            /** @var $model Order */
            $this->order = $repo->getBy(
                ['id' => $this->route('id')],
                withException: true,
                exceptionMessage: __("exceptions.orders.bs.not_found"),
                withTrashed: true
            );
        }

        return $this->order;
    }
}

