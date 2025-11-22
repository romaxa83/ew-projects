<?php

namespace App\Http\Requests\Customers;

use App\Enums\Customers\CustomerType;
use App\Enums\Tags\TagType;
use App\Foundations\Enums\EnumHelper;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Models\Tags\Tag;
use Illuminate\Validation\Rule;

class CustomerFilterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            $this->paginationRule(),
            $this->searchRule(),
            $this->idRule(),
            [
                'tag_id' => ['nullable', 'integer',
                    Rule::exists(Tag::class, 'id')
                        ->where('type', TagType::CUSTOMER),
                ],
                'types' => ['nullable', 'array'],
                'types.*' => ['required', 'string', EnumHelper::ruleIn(CustomerType::class)]
            ]
        );
    }
}

