<?php

namespace App\Http\Requests\Vehicles\Trucks;

use App\Http\Requests\Vehicles\VehicleIndexRequest;
use App\Models\Users\User;
use Illuminate\Validation\Rule;

class TruckIndexRequest extends VehicleIndexRequest
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'owner_id' => ['nullable', 'integer', Rule::exists(User::TABLE_NAME, 'id')],
            ]
        );
    }

    public function sortableAttributes(): array
    {
        return ['registration_expiration_date', 'inspection_expiration_date'];
    }
}
