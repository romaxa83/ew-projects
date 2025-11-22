<?php

namespace App\Services\Requests\ECom\Commands\Order\Parts;

use App\Models\Orders\Parts\Order;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class OrderUpdateCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        $this->assetIdForUri($data, 'id');

        return str_replace('{id}', $data['id'], config("requests.e_com.paths.order.parts.update"));
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Put;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Order */
        $tmp = [
            'id' => (string)$data->id,
            'is_paid' => $data->is_paid,
            'has_tax_exemption' => $data->with_tax_exemption,
            'delivery_cost' => $data->delivery_cost,
            'status' => $data->status->toUpperCase(),
            'payment_type' => $data->payment_method->toUpperCase(),
            'delivery_type' => $data->delivery_type?->toUpperCase(),
            'deliveryAddress' => [
                'first_name' => $data->delivery_address?->first_name,
                'last_name' => $data->delivery_address?->last_name,
                'company' => $data->delivery_address?->company,
                'address' => $data->delivery_address?->address,
                'city' => $data->delivery_address?->city,
                'state' => $data->delivery_address?->state,
                'zip_code' => $data->delivery_address?->zip,
                'phone' => $data->delivery_address?->phone->getValue(),
            ],
            'billingAddress' => [
                'first_name' => $data->billing_address?->first_name,
                'last_name' => $data->billing_address?->last_name,
                'company' => $data->billing_address?->company,
                'address' => $data->billing_address?->address,
                'city' => $data->billing_address?->city,
                'state' => $data->billing_address?->state,
                'zip_code' => $data->billing_address?->zip,
                'phone' => $data->billing_address?->phone->getValue(),
            ]
        ];

        foreach ($data->deliveries as $delivery){
            $tmp['deliveries'][] = [
                'guid' => (string)$delivery->id,
                'tracking_number' => $delivery->tracking_number,
                'method' => $delivery->method->toUpperCase(),
                'status' => $delivery->status->toUpperCase(),
            ];
        }
        foreach ($data->items as $item){
            $tmp['items'][] = [
                'guid' => (string)$item->inventory_id,
                'count' => (int)$item->qty,
                'cost' => $item->getPrice(),
            ];
        }

        return $tmp;
    }

    public function afterRequest(array $res): mixed
    {
        return $res['data'];
    }
}
