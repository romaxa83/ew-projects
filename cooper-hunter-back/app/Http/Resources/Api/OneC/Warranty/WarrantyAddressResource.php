<?php

namespace App\Http\Resources\Api\OneC\Warranty;

use App\Models\Warranty\WarrantyInfo\WarrantyAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WarrantyAddress
 */
class WarrantyAddressResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'country' => $this->country->country_code,
            'state' => $this->state->short_name,
            'city' => $this->city,
            'street' => $this->street,
            'zip' => $this->zip,
        ];
    }
}
