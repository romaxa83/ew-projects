<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Solutions;

use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionZoneEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Services\Catalog\Solutions\SolutionService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class SolutionBtuListQuery extends BaseQuery
{
    public const NAME = 'solutionBtuList';

    public function __construct(private SolutionService $service)
    {
    }

    public function type(): Type
    {
        return NonNullType::listOf(
            NonNullType::int()
        );
    }

    public function args(): array
    {
        return [
            'zone' => [
                'type' => SolutionZoneEnumType::nonNullType(),
            ],
            'series_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(
                        SolutionSeries::class,
                        'id'
                    )
                ]
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
        return $this->service->getBtuOutdoorList($args);
    }

}
