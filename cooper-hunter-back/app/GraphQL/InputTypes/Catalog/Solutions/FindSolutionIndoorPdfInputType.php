<?php


namespace App\GraphQL\InputTypes\Catalog\Solutions;


use App\Enums\Solutions\SolutionTypeEnum;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Solutions\Solution;
use Illuminate\Validation\Rule;

class FindSolutionIndoorPdfInputType extends BaseInputType
{
    public const NAME = 'FindSolutionIndoorPdfInputType';

    public function fields(): array
    {
        return [
            'indoor_id' => [
                'type' => NonNullType::id(),
                'description' => 'ID of indoor which have got in Query.findSolution',
                'rules' => [
                    'required',
                    'array',
                    Rule::exists(
                        Solution::class,
                        'id'
                    )
                        ->where(
                            'type',
                            SolutionTypeEnum::INDOOR
                        )
                ]
            ],
            'line_set_id' => [
                'type' => NonNullType::id(),
                'description' => 'ID of line set which have got in Query.findSolution',
                'rules' => [
                    'required',
                    'array',
                    Rule::exists(
                        Solution::class,
                        'id'
                    )
                        ->where(
                            'type',
                            SolutionTypeEnum::LINE_SET
                        )
                ]
            ]
        ];
    }
}
