<?php

namespace App\Foundations\Http\Requests\Common;

use App\Foundations\Http\Requests\BaseFormRequest;

class SearchRequest extends BaseFormRequest
{
    public const DEFAULT_LIMIT = 20;

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'required_without:id', 'string', 'min:3'],
            'id' => ['nullable', 'int'],
            'limit' => ['nullable', 'int'],
        ];
    }
}
