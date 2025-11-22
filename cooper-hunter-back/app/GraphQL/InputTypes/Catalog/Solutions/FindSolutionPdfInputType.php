<?php


namespace App\GraphQL\InputTypes\Catalog\Solutions;


use App\Enums\Solutions\SolutionTypeEnum;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Solutions\Solution;
use Illuminate\Validation\Rule;

class FindSolutionPdfInputType extends BaseInputType
{
    public const NAME = 'FindSolutionPdfInputType';

    public function fields(): array
    {
        return [
            'outdoor_id' => [
                'type' => NonNullType::id(),
                'description' => 'ID of outdoor which have got in Query.findSolution',
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(
                        Solution::class,
                        'id'
                    )
                        ->where(
                            'type',
                            SolutionTypeEnum::OUTDOOR
                        )
                ]
            ],
            'indoors' => [
                'type' => FindSolutionIndoorPdfInputType::nonNullList(),
            ]
        ];
    }
}
