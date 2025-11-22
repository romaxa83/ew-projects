<?php


namespace App\Http\Resources\Orders;


use Illuminate\Http\Resources\Json\JsonResource;

class OrderTimeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'from' => $this['from'] ?? null,
            'to' => $this['to']  ?? null,
        ];
    }
}
