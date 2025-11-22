<?php

namespace App\Services\OneC\Commands\Order;

use App\Contracts\Utilities\Dispatchable;
use App\Models\Companies\ShippingAddress;
use App\Models\Media\Media;
use App\Models\Orders\Dealer\Item;
use App\Models\Orders\Dealer\Order;
use App\Services\OneC\Commands\BaseCommand;

class BaseDealerOrderCommand extends BaseCommand
{
    public function transformData(Dispatchable $model, array $additions = []): array
    {
        /** @var $model Order */
        $media = [];
        foreach ($model->media as $item){
            /** @var $item Media */
            $media[] = $item->getFullUrl();
        }

        $items = [];
        foreach ($model->items as $k => $item){
            /** @var $item Item */
            $items[$k]['guid'] = $item->product->guid;
            $items[$k]['price'] = $item->price;
            $items[$k]['qty'] = $item->qty;
        }

        $shippingAddress = $model->shippingAddress;

        $location = [];

        if ($shippingAddress) {
            $location = [
                'name' => $model->shippingAddress->name,
                'phone' => $model->shippingAddress->phone?->getValue(),
                'fax' => $model->shippingAddress->fax?->getValue(),
                'email' => $model->shippingAddress->email?->getValue(),
                'receiving_persona' => $model->shippingAddress->receiving_persona,
                'country' => $model->shippingAddress->country->country_code,
                'state' => $model->shippingAddress->state->short_name,
                'city' => $model->shippingAddress->city,
                'address_line_1' => $model->shippingAddress->address_line_1,
                'address_line_2' => $model->shippingAddress->address_line_2,
                'po_box' => $model->shippingAddress->po_box,
                'zip' => $model->shippingAddress->zip,
            ];
        }

        return [
            'id' => $model->id,
            'guid' => $model->guid,
            'po' => $model->po,
            'delivery_type' => $model->delivery_type->value,
            'payment_type' => $model->payment_type->value,
            'type' => $model->type->value,
            'comment' => $model->comment,
            'media' => $media,
            'created_at' => $model->created_at?->format($this->formatDate),
            'payment_card_guid' => $model->paymentCard?->guid,
            'error' => $model->error,
            'company' => [
                'guid' => $model->dealer->company->guid
            ],
            'location' => $location,
            'products' => $items
        ];
    }

    protected function nameCommand(): string
    {
        return '';
    }

    protected function getUri(): string
    {
        return '';
    }
}
