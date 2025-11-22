<?php

namespace App\Http\Requests\Customers;

use App\Dto\Customers\CustomerDto;
use App\Dto\Customers\CustomerTaxExemptionEComDto;
use App\Enums\Customers\CustomerType;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Models\Customers\Customer;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="CustomerTaxExemptionUploadEComRequest",
 *     required={"link", "file_name"},
 *     @OA\Property(property="link", type="string", example="https://test.com/file.jpg"),
 *     @OA\Property(property="file_name", type="string", example="file.jpg"),
 * )
 */
class CustomerTaxExemptionUploadEComRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'link' => ['required', 'string', 'max:512'],
            'file_name' => ['required', 'string', 'max:512'],
        ];
    }

    public function getDto(): CustomerTaxExemptionEComDto
    {
        return CustomerTaxExemptionEComDto::byArgs($this->validated());
    }
}
