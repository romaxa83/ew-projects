<?php

namespace App\Http\Requests\Tags;

use App\Enums\Tags\TagType;
use App\Foundations\Http\Requests\BaseFormRequest;

class TagFilterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            $this->searchRule(),
            [
                'type' => ['nullable', 'string', TagType::ruleIn(),],
            ]
        );
    }
}
