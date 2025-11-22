<?php

namespace App\Http\Requests\Billing;

use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

class PaymentContactRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

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
        if ($this->boolean('use_accounting_contact')) {
            return [
                'use_accounting_contact' => ['required', 'boolean'],
            ];
        }

        return [
            'full_name' => ['required_if:use_accounting_contact,false', 'string', 'min:2', 'max:255'],
            'email' => ['required_if:use_accounting_contact,false', 'string', 'email', $this->email(), 'max:255'],
            'use_accounting_contact' => ['required', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge(
            [
                'use_accounting_contact' => $this->boolean('use_accounting_contact'),
            ]
        );
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'full_name.required_if' => trans('The Name field is required.'),
            'email.required_if' => trans('The Email field is required.'),
        ];
    }
}
