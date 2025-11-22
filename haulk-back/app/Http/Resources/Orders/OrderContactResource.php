<?php


namespace App\Http\Resources\Orders;


use Illuminate\Http\Resources\Json\JsonResource;

class OrderContactResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'full_name' => $this['full_name'] ?? null,
            'address' => $this['address']  ?? null,
            'city' => $this['city']  ?? null,
            'state_id' => $this['state_id']  ?? null,
            'zip' => $this['zip']  ?? null,
            'type_id' => $this['type_id']  ?? null,
            'timezone' => $this['timezone']  ?? null,
            'phone' => $this['phone']  ?? null,
            'phone_extension' => $this['phone_extension']  ?? null,
            'phone_name' => $this['phone_name']  ?? null,
            'email' => $this['email']  ?? null,
            'fax' => $this['fax']  ?? null,
            'comment' => $this['comment']  ?? null,
            'phones' => $this['phones']  ?? []
        ];
    }
}
