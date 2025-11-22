<?php

namespace App\Http\Requests\Library;

use App\Models\Library\LibraryDocument;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => [
                'required',
                'mimes:' . LibraryDocument::ALLOWED_FILE_TYPES,
                'max:' . LibraryDocument::MAX_FILE_SIZE,
            ],
            'user_id' => [
                'required_without:is_for_all_drivers',
                'integer',
                'exists:App\Models\Users\User,id'
            ],
            'is_for_all_drivers' => ['required_without:user_id', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.*' => trans('Please select a driver.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(
            [
                'is_for_all_drivers' => $this->boolean('is_for_all_drivers'),
            ]
        );
    }
}
