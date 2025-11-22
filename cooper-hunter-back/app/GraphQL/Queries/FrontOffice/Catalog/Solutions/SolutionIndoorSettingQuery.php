<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Solutions;

use App\Enums\Solutions\SolutionTypeEnum;
use App\GraphQL\Types\Catalog\Solutions\SolutionIndoorSettingType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Solutions\Solution;
use App\Services\Catalog\Solutions\SolutionService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class SolutionIndoorSettingQuery extends BaseQuery
{
    public const NAME = 'solutionIndoorSetting';

    public function __construct(private SolutionService $service)
    {
    }

    public function type(): Type
    {
        return SolutionIndoorSettingType::nonNullList();
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
        ];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->getIndoorSettingByOutdoor(
            Solution::find($args['outdoor_id'])
        );
    }

}
