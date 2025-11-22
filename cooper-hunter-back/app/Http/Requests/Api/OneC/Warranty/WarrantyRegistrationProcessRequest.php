<?php

namespace App\Http\Requests\Api\OneC\Warranty;

use App\Enums\Projects\Systems\WarrantyStatus;
use App\Enums\Warranties\WarrantyType;
use App\Http\Requests\BaseFormRequest;
use App\Models\Catalog\Products\Product;
use App\Models\Locations\State;
use App\Permissions\Warranty\WarrantyRegistration\WarrantyRegistrationUpdatePermission;
use Illuminate\Validation\Rule;

/**
 * @urlParam warranty int required The ID of the Warranty
 *
 * @bodyParam notice string Some notice for the warranty (denying reason)
 * @bodyParam serial_numbers object required
 */
class WarrantyRegistrationProcessRequest extends BaseFormRequest
{
    public const PERMISSION = WarrantyRegistrationUpdatePermission::KEY;

    public function rules(): array
    {
        return [
            'notice' => ['nullable', 'string'],
            'warranty_status' => ['nullable', WarrantyStatus::ruleIn()],
            'serial_numbers' => ['required', 'array'],
            'serial_numbers.*.product_guid' => ['required', Rule::exists(Product::class, 'guid')],
            'serial_numbers.*.serial_number' => ['required', 'string'],
            'type' => ['nullable', WarrantyType::ruleIn()],
            'user' => ['nullable', 'array'],
            'user.email' => ['nullable', 'string'],
            'user.first_name' => ['nullable', 'string'],
            'user.last_name' => ['nullable', 'string'],
            'user.company_name' => ['nullable', 'string'],
            'user.company_address' => ['nullable', 'string'],
            'product' => ['nullable', 'array'],
            'product.purchase_date' => ['nullable', 'string'],
            'product.purchase_place' => ['nullable', 'string'],
            'product.installation_date' => ['nullable', 'string'],
            'product.installation_license_number' => ['nullable', 'string'],
            'address' => ['nullable', 'array'],
            'address.state' => ['nullable', Rule::exists(State::class, 'short_name')],
            'address.city' => ['nullable', 'string'],
            'address.street' => ['nullable', 'string'],
            'address.zip' => ['nullable', 'string'],
        ];
    }
}
