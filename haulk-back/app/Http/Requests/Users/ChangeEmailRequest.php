<?php

namespace App\Http\Requests\Users;

use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Route;

class ChangeEmailRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
            'new_email' => ['required', 'email', $this->email(), 'unique:users,email',
                Rule::unique('change_emails', 'new_email')
                    ->ignore($this->user()->id, 'user_id'),
                'max:255'
            ],
            'user_id' => ['nullable', 'int', Rule::exists(User::TABLE_NAME, 'id')]
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->has('new_email')) {
            $this->merge(
                [
                    'new_email' => mb_convert_case($this->input('new_email'), MB_CASE_LOWER),
                ]
            );
        }
    }
}
