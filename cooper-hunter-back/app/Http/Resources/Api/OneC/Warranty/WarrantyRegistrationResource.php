<?php

namespace App\Http\Resources\Api\OneC\Warranty;

use App\Http\Resources\Api\OneC\Projects\SystemResource;
use App\Http\Resources\Api\OneC\Technicians\TechnicianResource;
use App\Models\Technicians\Technician;
use App\Models\Warranty\WarrantyRegistration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WarrantyRegistration
 */
class WarrantyRegistrationResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'technician_verified' => $this->member instanceof Technician
                && $this->member->is_verified,
            'notice' => $this->notice,
            'warranty_status' => $this->warranty_status,
            'type' => $this->type,
            'user_info' => $this->user_info,
            'address_info' => WarrantyAddressResource::make($this->address),
            'product_info' => $this->product_info->datesAsTimestamp(),
            'created_at' => $this->created_at->getTimestamp(),
            'units' => WarrantyRegistrationUnitsResource::collection($this->unitsPivot),
            'technician' => TechnicianResource::make($this->member),
            'system' => SystemResource::make($this->system)
        ];
    }
}
