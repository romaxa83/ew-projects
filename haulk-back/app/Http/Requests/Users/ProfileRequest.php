<?php

namespace App\Http\Requests\Users;

use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:191', 'alpha_spaces'],
            'phone' => ['nullable', 'string', $this->USAPhone(), 'max:191'],
            'phone_extension' => ['nullable', 'string', 'max:191'],
            'phones' => ['array', 'nullable'],
            'phones.*.number' => ['nullable', 'string', $this->USAPhone(), 'max:191'],
            'phones.*.extension' => ['nullable', 'string', 'max:191'],
            'first_name' => ['nullable'],
            'last_name' => ['nullable'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'full_name.alpha_spaces'  => 'The full name can contain only English characters.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->splitName();
    }

    protected function splitName():void
    {
        if ($this->has('full_name') && !empty($this->input('full_name'))) {
            $data = explode(' ', $this->input('full_name'), 2);
            $this->merge(
                [
                    'first_name' => $data[0] ?? '',
                    'last_name' => $data[1] ?? '',
                ]
            );
        }
    }
}
