<?php

namespace App\GraphQL\InputTypes\Content\OurCases;

use App\GraphQL\Types\NonNullType;
use App\Models\Content\OurCases\OurCase;
use Illuminate\Validation\Rule;

class OurCaseUpdateInput extends OurCaseCreateInput
{
    public const NAME = 'OurCaseUpdateInput';

    public function fields(): array
    {
        return [
                'id' => [
                    'type' => NonNullType::id(),
                    'rules' => [Rule::exists(OurCase::TABLE, 'id')],
                ],
            ] + parent::fields();
    }
}
