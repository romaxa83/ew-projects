<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Solutions;

use App\Dto\Catalog\Solutions\FindSolutionDto;
use App\GraphQL\Types\Catalog\Solutions\FindSolutionType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionClimateZoneEnumType;
use App\GraphQL\Types\Enums\Catalog\Solutions\SolutionZoneEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Rules\Catalog\Solution\BtuRule;
use App\Rules\Catalog\Solution\CountZonesRule;
use App\Services\Catalog\Solutions\SolutionService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class FindSolutionQuery extends BaseQuery
{
    public const NAME = 'findSolution';

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
            'zone' => [
                'type' => SolutionZoneEnumType::nonNullType(),
            ],
            'count_zones' => [
                'type' => NonNullType::int(),
                'description' => 'If zone is SINGLE you have to use default value, else you have to set value, between 2 and 6.',
                'defaultValue' => 1
            ],
            'climate_zones' => [
                'type' => NonNullType::listOf(
                    SolutionClimateZoneEnumType::nonNullType()
                ),
            ],
            'series_id' => [
                'type' => NonNullType::id(),
                'description' => 'Series ID (Sophia/Hyper etc.).',
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(SolutionSeries::class, 'id')
                ]
            ],
            'btu' => [
                'type' => NonNullType::int(),
                'description' => 'BTU number.'
            ],
            'voltage' => [
                'type' => NonNullType::int(),
                'description' => 'Voltage of outdoor (115/230)',
                'defaultValue' => config('catalog.solutions.voltage.default'),
                'rules' => [
                    'required',
                    'int',
                    Rule::in(
                        config('catalog.solutions.voltage.list')
                    )
                ]
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
                new CountZonesRule($args)
            ],
            'btu' => [
                'required',
                'int',
                'min:6000',
                'max:60000',
                new BtuRule($args)
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
        return $this->service->find(
            FindSolutionDto::byArgs($args)
        );
    }

}
