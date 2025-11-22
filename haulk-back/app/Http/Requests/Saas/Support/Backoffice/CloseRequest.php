<?php

namespace App\Http\Requests\Saas\Support\Backoffice;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class CloseRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('backofficeClose', $this->route()->parameter('supportRequest'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'closing_reason' => [
                'required',
                'string'
            ]
        ];
    }

    public function closingReason(): string
    {
        return $this->validated()['closing_reason'];
    }
}
