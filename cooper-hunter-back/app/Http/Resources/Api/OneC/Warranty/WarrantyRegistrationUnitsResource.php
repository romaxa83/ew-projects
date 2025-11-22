<?php

namespace App\Http\Resources\Api\OneC\Warranty;

use App\Models\Warranty\WarrantyRegistrationUnitPivot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WarrantyRegistrationUnitPivot
 */
class WarrantyRegistrationUnitsResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'product_id' => $this->product->id,
            'product_guid' => $this->product->guid,
            'product_title' => $this->product->title,
            'serial_number' => $this->serial_number,
        ];
    }
}
