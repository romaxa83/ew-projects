<?php

namespace App\Http\Requests\Webhooks\Vehicle;

use App\Foundations\Http\Requests\BaseFormRequest;

class UpdateOrCreateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'data.id' => ['required', 'integer'],
            'data.vehicle_type' => ['required', 'string'],
            'data.vin' => ['required', 'string'],
            'data.unit_number' => ['required', 'string'],
            'data.make' => ['required', 'string'],
            'data.model' => ['required', 'string'],
            'data.year' => ['required', 'string'],
            'data.color' => ['nullable', 'string'],
            'data.gvwr' => ['nullable', 'numeric'],
            'data.type' => ['nullable', 'integer'],
            'data.license_plate' => ['nullable', 'string'],
            'data.temporary_plate' => ['nullable', 'string'],
            'data.notes' => ['nullable', 'string'],
            'data.created_at' => ['required', 'integer'],
            'data.company' => ['required', 'array'],
            'data.company.id' => ['required', 'integer'],
            'data.company.name' => ['required', 'string'],
            'data.customer' => ['required', 'array'],
            'data.customer.id' => ['required', 'integer'],
            'data.customer.first_name' => ['required', 'string'],
            'data.customer.last_name' => ['required', 'string'],
            'data.customer.phone' => ['nullable', 'string'],
            'data.customer.phone_extension' => ['nullable', 'string'],
            'data.customer.phones' => ['nullable'],
            'data.customer.email' => ['required', 'string', 'email'],
            'data.tags' => ['array'],
            'data.tags.*.id' => ['required', 'integer'],
            'data.tags.*.name' => ['required', 'string'],
            'data.tags.*.color' => ['required', 'string'],
            'data.media' => ['array'],
            'data.media.*.id' => ['required', 'integer'],
            'data.media.*.model_type' => ['required', 'string'],
            'data.media.*.model_id' => ['required', 'integer'],
            'data.media.*.name' => ['required', 'string'],
            'data.media.*.file_name' => ['required', 'string'],
            'data.media.*.mime_type' => ['required', 'string'],
            'data.media.*.disk' => ['required', 'string'],
            'data.media.*.size' => ['required', 'integer'],
            'data.media.*.manipulations' => ['array'],
            'data.media.*.custom_properties' => ['array'],
            'data.media.*.responsive_images' => ['array'],
            'data.media.*.order_column' => ['required', 'integer'],
        ];
    }
}

