<?php

namespace App\Http\Requests\Saas\Support;

use App\Permissions\Saas\Support\SupportRequestShow;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class IndexMessageRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('support-requests read') || $this->user()->can(SupportRequestShow::KEY);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'per_page' => data_get($this, 'per_page', 10)
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'older_than' => [
                'nullable',
                'integer'
            ],
            'newer_than' => [
                'nullable',
                'integer'
            ],
            'per_page' => [
                'nullable',
                'integer'
            ],
        ];
    }
}
