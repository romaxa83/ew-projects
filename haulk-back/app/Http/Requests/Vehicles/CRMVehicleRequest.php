<?php

namespace App\Http\Requests\Vehicles;

use App\Models\Vehicles\Vehicle;

abstract class CRMVehicleRequest extends VehicleRequest
{
    protected const MAX_ATTACHMENTS_COUNT = 10;

    public function rules(): array
    {
        return  array_merge(
            parent::rules(),
            [
                'registration_number' => ['nullable', 'string', 'alpha_dash', 'max:16'],
                'registration_date' => ['nullable', 'string', 'date_format:m/d/Y'],
                'registration_expiration_date' => ['nullable', 'string', 'date_format:m/d/Y'],
                Vehicle::REGISTRATION_DOCUMENT_NAME => ['file', $this->attachmentTypes(), 'max:10240'],

                'inspection_number' => ['nullable', 'string', 'alpha_dash', 'max:16'],
                'inspection_date' => ['nullable', 'string', 'date_format:m/d/Y'],
                'inspection_expiration_date' => ['nullable', 'string', 'date_format:m/d/Y'],
                Vehicle::INSPECTION_DOCUMENT_NAME => ['file', $this->attachmentTypes(), 'max:10240'],
            ]
        );
    }
}
