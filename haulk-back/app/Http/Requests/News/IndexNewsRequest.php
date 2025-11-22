<?php

namespace App\Http\Requests\News;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class IndexNewsRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('news');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'order_type' => [
                'nullable',
                'in:asc,desc'
            ],
            'per_page' => [
                'nullable',
                'integer'
            ],
            'name' => [
                'nullable',
                'string'
            ],
            'date_from' => [
                'nullable',
                'string'
            ],
            'date_to' => [
                'nullable',
                'string'
            ]
        ];
    }

    public function validated(): array
    {
        $validated = parent::validated();

        data_fill($validated, 'order_type', 'desc');
        data_fill($validated, 'per_page', 10);

        return $validated;
    }
}
