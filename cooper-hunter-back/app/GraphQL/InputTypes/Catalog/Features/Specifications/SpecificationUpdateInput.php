<?php

namespace App\GraphQL\InputTypes\Catalog\Features\Specifications;

use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Specification;
use Illuminate\Validation\Rule;

class SpecificationUpdateInput extends SpecificationCreateInput
{
    public const NAME = 'SpecificationUpdateInput';

    public function fields(): array
    {
        return array_merge(
            [
                'id' => [
                    'type' => NonNullType::id(),
                    'rule' => [Rule::exists(Specification::TABLE, 'id')],
                ]
            ],
            parent::fields(),
        );
    }
}
