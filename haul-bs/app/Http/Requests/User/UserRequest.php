<?php

namespace App\Http\Requests\User;

use App\Dto\Users\UserDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Rules\PhoneRule;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Users\User;
use Route;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="UserRequest",
 *     required={"email", "role_id", "first_name", "last_name"},
 *     @OA\Property(property="role_id", type="int", description="Role id", example="1"),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", example="example@gmail.com"),
 *     @OA\Property(property="phone", type="string", example="1555999999", nullable=true),
 *     @OA\Property(property="phone_extension", type="string", example="9999", nullable=true),
 *     @OA\Property(property="phones", type="array", @OA\Items(ref="#/components/schemas/PhonesRaw"), nullable=true),
 * )
 */

class UserRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        $id = Route::getCurrentRoute()->parameter('id');

        $rules = [
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'first_name' => ['required', 'string', 'max:191', 'alpha_spaces'],
            'last_name' => ['required', 'string', 'max:191', 'alpha_spaces'],
            'email' => ['required', 'email', Rule::unique(User::TABLE, 'email'), 'max:191'],
            'phone' => ['nullable', 'string', new PhoneRule(), 'max:191'],
            'phone_extension' => ['nullable', 'string', 'max:191'],
            'phones' => ['array', 'nullable'],
            'phones.*.number' => ['nullable', 'string', new PhoneRule(), 'max:191'],
            'phones.*.extension' => ['nullable', 'string', 'max:191'],
        ];

        if($id){
            $rules['email'] = ['required', 'email', Rule::unique(User::TABLE, 'email')->ignore($id), 'max:191'];
        }

        return $rules;
    }

    public function getDto(): UserDto
    {
        return UserDto::byArgs($this->validated());
    }
}
