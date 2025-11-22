<?php

namespace App\GraphQL\InputTypes\Catalog\Solutions;

use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionIndoorEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use Illuminate\Validation\Rule;

class FindSolutionIndoorInputType extends BaseInputType
{
    public const NAME = 'FindSolutionIndoorInputType';

    public function fields(): array
    {
        return [
            'btu' => [
                'type' => NonNullType::int(),
            ],
            'series_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(SolutionSeries::class, 'id'),
                ],
            ],
            'type' => [
                'type' => SolutionIndoorEnumType::nonNullType(),
            ],
        ];
    }
}
