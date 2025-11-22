<?php

namespace App\Http\Requests\Customers;

use App\Dto\Customers\CustomerDto;
use App\Enums\Tags\TagType;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Modules\Permission\Roles\SalesManagerRole;
use App\Foundations\Rules\PhoneRule;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use App\Rules\User\UserAsRole;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="CustomerRequest",
 *     required={"first_name", "last_name", "phone", "email"},
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="phone", type="string", example="1555555555"),
 *     @OA\Property(property="phone_extension", type="string", example="234"),
 *     @OA\Property(property="phones", type="array", description="aditional phones",
 *         @OA\Items(ref="#/components/schemas/PhonesRaw")
 *     ),
 *     @OA\Property(property="email", type="string", example="jack@mail.com"),
 *     @OA\Property(property="notes", type="string", example="some text"),
 *     @OA\Property(property="tags", type="array", description="Tag id list", example={1, 22, 3},
 *         @OA\Items(type="integer")
 *     ),
 *     @OA\Property(property="attachment_files", type="array",
 *          @OA\Items(type="file")
 *     ),
 *     @OA\Property(property="sales_manager_id", type="integer", example="4"),
 * )
 */
class CustomerRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'first_name' => ['required', 'string', 'max:191', 'alpha'],
            'last_name' => ['required', 'string', 'max:191', 'alpha'],
            'phone' => ['nullable', 'string', new PhoneRule(), 'max:191',
                $id
                    ? Rule::unique(Customer::TABLE, 'phone')
//                        ->where(fn($query) => $query->where('from_haulk', false))
                        ->ignore($id)
                    : Rule::unique(Customer::TABLE, 'phone')
//                        ->where(fn($query) => $query->where('from_haulk', false))
            ],
            'phone_extension' => ['nullable', 'string', 'max:191'],
            'phones' => ['array', 'nullable'],
            'phones.*.number' => ['nullable', new PhoneRule(), 'string', 'max:191'],
            'phones.*.extension' => ['nullable', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191',
                $id
                    ? Rule::unique(Customer::TABLE, 'email')
//                        ->where(fn($query) => $query->where('from_haulk', false))
                        ->ignore($id)
                    : Rule::unique(Customer::TABLE, 'email')
//                        ->where(fn($query) => $query->where('from_haulk', false))
            ],
            'notes' => ['nullable', 'string'],
            'tags' => ['nullable' ,'array', 'max:5'],
            'tags.*' => ['required', 'int',
                Rule::exists(Tag::TABLE, 'id')
                    ->where('type', TagType::CUSTOMER)
            ],
            Customer::ATTACHMENT_FIELD_NAME => ['nullable', 'array'],
            Customer::ATTACHMENT_FIELD_NAME . '.*' => $this->fileRule(),
            'sales_manager_id' => ['bail', 'nullable', 'integer',
                Rule::exists(User::TABLE, 'id'),
                new UserAsRole(SalesManagerRole::NAME)
            ],
        ];
    }

    public function getDto(): CustomerDto
    {
        return CustomerDto::byArgs($this->validated());
    }


    public function messages()
    {
        $msg = __('validation.custom.customer.exist');
        if(isset($this->all()['email'])){
            $email = $this->all()['email'];
            $customer = Customer::where('email', $email)->first();
            if($customer?->salesManager){
                $msg = __('validation.custom.customer.exist_and_has_manager', [
                    'sales_manager_name' => $customer->salesManager->full_name,
                    'sales_manager_email' => $customer->salesManager->email->getValue(),
                ]);
            }
        }

        return [
            'email.unique' => $msg,
        ];
    }
}
