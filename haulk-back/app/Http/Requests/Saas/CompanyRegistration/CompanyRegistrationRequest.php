<?php

namespace App\Http\Requests\Saas\CompanyRegistration;

use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Rules\Usdot\UsdotValidator;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRegistrationRequest extends FormRequest
{
    use ValidationRulesTrait;
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'usdot' => [
                'required',
                'digits_between:6,8',
                Rule::unique(Company::TABLE_NAME, 'usdot'),
                new UsdotValidator()
            ],
            'ga_id' => ['nullable', 'string'],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => [
                'required',
                'email',
                Rule::unique(Company::TABLE_NAME, 'email'),
                Rule::unique(User::TABLE_NAME, 'email'),
            ],
            'phone' => ['nullable', 'string', $this->USAPhone()],
            'password' => ['required', 'string', $this->passwordRule()],
            'password_confirmation' => ['required', 'string', 'same:password', $this->passwordRule()],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(
                [
                    'email' => mb_convert_case($this->input('email'), MB_CASE_LOWER),
                ]
            );
        }
    }
}
