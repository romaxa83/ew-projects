<?php

namespace App\GraphQL\InputTypes\Content\OurCaseCategories;

use App\GraphQL\Types\NonNullType;
use App\Models\Content\OurCases\OurCaseCategory;
use Illuminate\Validation\Rule;

class OurCaseCategoryUpdateInput extends OurCaseCategoryCreateInput
{
    public const NAME = 'OurCaseCategoryUpdateInput';

    public function fields(): array
    {
        return [
                'id' => [
                    'type' => NonNullType::id(),
                    'rules' => [Rule::exists(OurCaseCategory::TABLE, 'id')],
                ],
            ] + parent::fields();
    }
}
