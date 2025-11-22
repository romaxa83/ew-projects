<?php

namespace App\Http\Resources\Vehicles;

use App\Http\Resources\Tags\TagShortResource;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiclePaginationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vin' => $this->vin,
            'unit_number' => $this->unit_number,
            'license_plate' => $this->license_plate,
            'temporary_plate' => $this->temporary_plate,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'type' => $this->type,
            'owner_name' => $this->customer->full_name ?? null,
            'customer_id' => $this->customer_id ?? null,
            'tags' => TagShortResource::collection($this->tags),
            'company_name' => $this->company->name ?? null,
            'hasRelatedOpenOrders' => $this->hasRelatedOpenOrders(),
            'hasRelatedDeletedOrders' => $this->hasRelatedDeletedOrders(),
            'comments_count' => $this->comments->count(),
            'color' => $this->color,
            'gvwr' => $this->gvwr,
        ];
    }
}
