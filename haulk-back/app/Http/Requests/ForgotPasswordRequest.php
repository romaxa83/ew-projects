<?php


namespace App\Http\Requests;


use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    use OnlyValidateForm, ValidationRulesTrait;

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'exists:users,email',
                $this->email(),
                'max:191'
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
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
