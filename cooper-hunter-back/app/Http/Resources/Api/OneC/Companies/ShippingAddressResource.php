<?php

namespace App\Http\Resources\Api\OneC\Companies;

use App\Models\Companies\ShippingAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ShippingAddress
 */
class ShippingAddressResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'active' => $this->active,
            'name' => $this->name,
            'phone' => $this->phone?->getValue(),
            'fax' => $this->fax?->getValue(),
            'email' => $this->email?->getValue(),
            'receiving_persona' => $this->receiving_persona,
            'country' => $this->country->country_code,
            'state' => $this->state->short_name,
            'city' => $this->city,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'zip' => $this->zip,
        ];
    }
}
