<?php

namespace App\Http\Resources\Vehicles;

use App\Http\Resources\Customers\CustomerShortListResource;
use App\Http\Resources\Files\FileResource;
use App\Http\Resources\Tags\TagShortResource;
use App\Models\Vehicles\Vehicle;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
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
            'owner' => CustomerShortListResource::make($this->customer),
            'tags' => TagShortResource::collection($this->tags),
            'notes' => $this->notes,
            'company_name' => $this->company->name ?? null,
            'hasRelatedOpenOrders' => $this->hasRelatedOpenOrders(),
            'hasRelatedDeletedOrders' => $this->hasRelatedDeletedOrders(),
            Vehicle::ATTACHMENT_COLLECTION_NAME => FileResource::collection($this->getAttachments()),
            'color' => $this->color,
            'gvwr' => $this->gvwr,
        ];
    }
}
