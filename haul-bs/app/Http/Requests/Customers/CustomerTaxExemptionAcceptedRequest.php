<?php

namespace App\Http\Requests\Customers;

use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;

/**
 * @OA\Schema(type="object", title="CustomerTaxExemptionRequest",
 *     required={"date_active_to"},
 *     @OA\Property(property="date_active_to", type="string", example="date format m/d/Y"),
 * )
 */
class CustomerTaxExemptionAcceptedRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {

        return [
            'date_active_to' => ['required', 'string', 'date_format:m/d/Y'],
        ];
    }
}
