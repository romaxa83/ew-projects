<?php

namespace App\Http\Requests\Customers;

use App\Dto\Customers\CustomerTaxExemptionDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;

/**
 * @OA\Schema(type="object", title="CustomerTaxExemptionRequest",
 *     required={"date_active_to", "file"},
 *     @OA\Property(property="date_active_to", type="string", example="date format m/d/Y"),
 *     @OA\Property(property="file",type="string", format="binary", nullable=true , description="The file to upload"),
 * )
 */
class CustomerTaxExemptionRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'date_active_to' => ['required', 'string', 'date_format:m/d/Y'],
            'file' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:1024'],
        ];
    }

    public function getDto(): CustomerTaxExemptionDto
    {
        return CustomerTaxExemptionDto::byArgs($this->validated());
    }
}
