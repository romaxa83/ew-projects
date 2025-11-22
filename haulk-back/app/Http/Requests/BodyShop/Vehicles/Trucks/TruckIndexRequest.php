<?php

namespace App\Http\Requests\BodyShop\Vehicles\Trucks;

use App\Http\Requests\Vehicles\VehicleIndexRequest;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use Illuminate\Validation\Rule;

class TruckIndexRequest extends VehicleIndexRequest
{
    public function rules(): array
    {
         return array_merge(
             parent::rules(),
             [
                 'driver_id' => ['nullable', 'integer'],
                 'customer_id' => ['nullable', 'integer', Rule::exists(VehicleOwner::class, 'id')],
             ],
         );
    }

    protected function orderTypeIn(): string
    {
        return 'in:' . implode(',', ['asc', 'desc']);
    }

    protected function orderByIn(): string
    {
        return 'in:' . implode(',', $this->sortableAttributes());
    }

    public function sortableAttributes(): array
    {
        return ['company_name'];
    }
}
