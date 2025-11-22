<?php

namespace App\Http\Requests\Tags;

use App\Models\Tags\Tag;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class IndexTagRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'q' => [
                'nullable',
                'string',
            ],
            'type' => [
                'nullable',
                'string',
                $this->typeRuleIn(),
            ],
        ];
    }

    private function typeRuleIn(): string
    {
        return 'in:' . implode(',', Tag::TYPES);
    }
}
