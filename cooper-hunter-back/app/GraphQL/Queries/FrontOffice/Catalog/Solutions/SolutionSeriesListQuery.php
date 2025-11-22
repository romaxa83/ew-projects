<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Solutions;

use App\GraphQL\Types\Catalog\Solutions\SolutionSeriesType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionClimateZoneEnumType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionZoneEnumType;
use App\GraphQL\Types\NonNullType;
use App\Services\Catalog\Solutions\SolutionService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class SolutionSeriesListQuery extends BaseQuery
{
    public const NAME = 'solutionSeriesList';

    public function __construct(private SolutionService $service)
    {
    }

    public function type(): Type
    {
        return SolutionSeriesType::nonNullList();
    }

    public function args(): array
    {
        return [
            'zone' => [
                'type' => SolutionZoneEnumType::nonNullType(),
            ],
            'climate_zones' => [
                'type' => NonNullType::listOf(
                    SolutionClimateZoneEnumType::nonNullType()
                ),
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
        return $this->service->getSeriesOutdoorList($args);
    }

}
