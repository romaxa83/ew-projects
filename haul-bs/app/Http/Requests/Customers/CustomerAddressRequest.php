<?php

namespace App\Http\Requests\Customers;

use App\Dto\Customers\AddressDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Rules\PhoneRule;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Customers;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="CustomerAddressRequest",
 *     required={"is_default", "first_name", "last_name", "phone", "address", "city", "state", "zip"},
 *     @OA\Property(property="is_default", type="boolean", example="true"),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="company_name", type="string", example="Sony Inc."),
 *     @OA\Property(property="address", type="string", example="801 West Dundee Road"),
 *     @OA\Property(property="city", type="string", example="Arlington Heights"),
 *     @OA\Property(property="state", type="string", example="CA"),
 *     @OA\Property(property="zip", type="string", example="60004"),
 *     @OA\Property(property="phone", type="string", example="1555555555"),
 * )
 */
class CustomerAddressRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        $id = $this->route('addressId');

        return [
            'is_default' => ['required', 'boolean'],
            'first_name' => ['required', 'string', 'max:191', 'alpha'],
            'last_name' => ['required', 'string', 'max:191', 'alpha'],
            'company_name' => ['nullable', 'string', 'max:191'],
            'address' => ['required', 'string', 'max:191'],
            'city' => ['required', 'string', 'max:191'],
            'state' => ['required', 'string', 'max:191'],
            'zip' => ['required', 'string', 'max:191'],
            'phone' => ['required', 'string', new PhoneRule(), 'max:191'],
        ];
    }

    public function getDto(): AddressDto
    {
        return AddressDto::byArgs($this->validated());
    }
}
