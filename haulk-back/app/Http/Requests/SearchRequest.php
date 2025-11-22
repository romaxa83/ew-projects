<?php

namespace App\Http\Requests;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public const DEFAULT_LIMIT = 20;

    use OnlyValidateForm;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'required_without:searchid', 'string', 'min:3'],
            'searchid' => ['nullable', 'int'],
        ];
    }
}
