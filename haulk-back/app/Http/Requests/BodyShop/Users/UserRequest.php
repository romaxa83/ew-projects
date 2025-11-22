<?php

namespace App\Http\Requests\BodyShop\Users;

use App\Dto\UserDto;
use App\Models\Users\User;
use App\Traits\Requests\ContactTransformerTrait;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Route;

/**
 * @property string email
 */
class UserRequest extends FormRequest
{
    use ContactTransformerTrait;
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function rules(): array
    {
        $user = Route::getCurrentRoute()->parameter('user');

        $rules = [
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')
                    ->whereIn('name', User::BS_ROLES)
            ],
            'first_name' => ['required', 'string', 'max:191', 'alpha_spaces'],
            'last_name' => ['required', 'string', 'max:191', 'alpha_spaces'],
            'phone' => ['nullable', 'string', $this->USAPhone(), 'max:191'],
            'phone_extension' => ['nullable', 'string', 'max:191'],
            'phones' => ['array', 'nullable'],
            'phones.*.number' => ['nullable', $this->USAPhone(), 'string', 'max:191'],
            'phones.*.extension' => ['nullable', 'string', 'max:191'],
        ];

        if ($user) {
            $rules['email'] = ['required', 'email', $this->email(), 'unique:users,email,' . $user->id, 'max:191'];
        } else {
            $rules['email'] = ['required', 'email', $this->email(), 'unique:users,email', 'max:191'];
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->transformPhoneAttribute('phone');

        if ($this->has('email')) {
            $this->merge(
                [
                    'email' => mb_convert_case($this->input('email'), MB_CASE_LOWER),
                ]
            );
        }
    }

    public function getDto(): UserDto
    {
        return UserDto::byParams($this->validated());
    }
}
