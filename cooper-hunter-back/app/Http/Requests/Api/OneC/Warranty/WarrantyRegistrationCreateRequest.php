<?php

namespace App\Http\Requests\Api\OneC\Warranty;

use App\Enums\Projects\Systems\WarrantyStatus;
use App\Enums\Warranties\WarrantyType;
use App\Http\Requests\BaseFormRequest;
use App\Models\Catalog\Products\Product;
use App\Models\Commercial\CommercialProject;
use App\Models\Locations\State;
use App\Models\Projects\System;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Illuminate\Validation\Rule;

/**
 *
 * @bodyParam notice string Some notice for the warranty (denying reason)
 * @bodyParam serial_numbers object required
 * @bodyParam user object required
 * @bodyParam product object required
 * @bodyParam address object required
 */
class WarrantyRegistrationCreateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'notice' => ['nullable', 'string'],
            'warranty_status' => ['required', WarrantyStatus::ruleIn()],
            'system_id' => ['nullable', Rule::exists(System::class, 'id')],
            'commercial_project_guid' => ['nullable', Rule::exists(CommercialProject::class, 'guid')],
            'user_type' => ['nullable', Rule::in([User::MORPH_NAME, Technician::MORPH_NAME])],
            'user_guid' => ['nullable'],
            'serial_numbers' => ['required', 'array'],
            'serial_numbers.*.product_guid' => ['required', Rule::exists(Product::class, 'guid')],
            'serial_numbers.*.serial_number' => ['required', 'string'],
            'type' => ['required', WarrantyType::ruleIn()],
            'user' => ['required', 'array'],
            'user.email' => ['required', 'string'],
            'user.first_name' => ['required', 'string'],
            'user.last_name' => ['required', 'string'],
            'user.company_name' => ['nullable', 'string'],
            'user.company_address' => ['nullable', 'string'],
            'product' => ['required', 'array'],
            'product.purchase_date' => ['required', 'string'],
            'product.purchase_place' => ['required', 'string'],
            'product.installation_date' => ['required', 'string'],
            'product.installation_license_number' => ['required', 'string'],
            'address' => ['required', 'array'],
            'address.state' => ['required', Rule::exists(State::class, 'short_name')],
            'address.city' => ['required', 'string'],
            'address.street' => ['required', 'string'],
            'address.zip' => ['required', 'string'],
        ];
    }
}

