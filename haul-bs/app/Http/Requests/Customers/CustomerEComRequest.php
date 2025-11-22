<?php

namespace App\Http\Requests\Customers;

use App\Dto\Customers\CustomerDto;
use App\Dto\Customers\CustomerEcomDto;
use App\Enums\Customers\CustomerType;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Models\Customers\Customer;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="CustomerEComRequest",
 *     required={"first_name", "last_name", "email"},
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", example="jack@mail.com"),
 * )
 */
class CustomerEComRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:191', 'alpha'],
            'last_name' => ['required', 'string', 'max:191', 'alpha'],
            'email' => ['required', 'email', 'max:191'],
        ];
    }

    public function getDto(): CustomerEcomDto
    {
        return CustomerEcomDto::byArgs($this->validated());
    }
}
