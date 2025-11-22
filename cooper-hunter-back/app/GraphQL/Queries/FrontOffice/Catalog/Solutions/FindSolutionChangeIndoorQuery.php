<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Solutions;

use App\Dto\Catalog\Solutions\FindSolutionChangeIndoorDto;
use App\Enums\Solutions\SolutionTypeEnum;
use App\GraphQL\InputTypes\Catalog\Solutions\FindSolutionIndoorInputType;
use App\GraphQL\Types\Catalog\Solutions\FindSolutionType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Solutions\Solution;
use App\Rules\Catalog\Solution\BtuRule;
use App\Services\Catalog\Solutions\SolutionService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class FindSolutionChangeIndoorQuery extends BaseQuery
{
    public const NAME = 'findSolutionChangeIndoor';

    public function __construct(private SolutionService $service)
    {
    }

    public function type(): Type
    {
        return FindSolutionType::nonNullType();
    }

    public function args(): array
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
            'count_zones' => [
                'type' => NonNullType::int(),
                'description' => 'If zone is SINGLE you have to use default value, else you have to set value, between 2 and 6.',
                'defaultValue' => 1
            ],
            'indoors' => [
                'type' => FindSolutionIndoorInputType::nonNullList()
            ]
        ];
    }

    public function rules(array $args = []): array
    {
        return [
            'count_zones' => [
                'required',
                'int',
                'min:1',
                'max:6',
            ],
            'indoors' => [
                'array',
                'required',
                new BtuRule($args, SolutionTypeEnum::INDOOR)
            ]
        ];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->changeIndoor(
            FindSolutionChangeIndoorDto::byArgs($args)
        );
    }

}
