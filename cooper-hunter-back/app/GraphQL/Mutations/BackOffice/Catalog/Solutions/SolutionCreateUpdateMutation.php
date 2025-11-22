<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Solutions;

use App\Dto\Catalog\Solutions\SolutionDto;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\GraphQL\InputTypes\Catalog\Solutions\FindSolutionInputType;
use App\GraphQL\Types\Catalog\Products\ProductType;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Models\Catalog\Solutions\Solution;
use App\Permissions\Catalog\Solutions\SolutionCreateUpdatePermission;
use App\Rules\Catalog\Solution\BtuRule;
use App\Rules\Catalog\Solution\DefaultSchemasRule;
use App\Services\Catalog\Solutions\SolutionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SolutionCreateUpdateMutation extends BaseMutation
{
    public const NAME = 'solutionCreateUpdate';
    public const PERMISSION = SolutionCreateUpdatePermission::KEY;

    public function __construct(protected SolutionService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ProductType::nonNullType();
    }

    public function args(): array
    {
        return [
            'solution' => [
                'type' => FindSolutionInputType::nonNullType()
            ]
        ];
    }

    public function rules(array $args = []): array
    {
        return [
            'solution.product_id' => [
                'required',
                'int',
                Rule::exists(Product::class, 'id')
            ],
            'solution.short_name' => [
                'nullable',
                'string',
                Rule::requiredIf(
                    fn() => $args['solution']['type'] === SolutionTypeEnum::LINE_SET
                ),
            ],
            'solution.series_id' => [
                'nullable',
                'int',
                Rule::requiredIf(
                    fn() => $args['solution']['type'] !== SolutionTypeEnum::LINE_SET
                ),
                Rule::exists(SolutionSeries::class, 'id')
            ],
            'solution.zone' => [
                'nullable',
                'string',
                Rule::requiredIf(
                    fn() => $args['solution']['type'] === SolutionTypeEnum::OUTDOOR
                ),
            ],
            'solution.climate_zones' => [
                'nullable',
                'array',
                Rule::requiredIf(
                    fn() => $args['solution']['type'] === SolutionTypeEnum::OUTDOOR
                ),
            ],
            'solution.indoor_type' => [
                'nullable',
                'string',
                Rule::requiredIf(
                    fn() => $args['solution']['type'] === SolutionTypeEnum::INDOOR
                ),
            ],
            'solution.btu' => [
                'nullable',
                'int',
                Rule::requiredIf(
                    fn() => $args['solution']['type'] !== SolutionTypeEnum::LINE_SET
                ),
                new BtuRule($args['solution'], null)
            ],
            'solution.voltage' => [
                'nullable',
                'int',
                Rule::requiredIf(
                    fn() => $args['solution']['type'] === SolutionTypeEnum::OUTDOOR
                ),
                Rule::in(
                    config('catalog.solutions.voltage.list')
                )
            ],
            'solution.line_sets' => [
                'nullable',
                'array',
                Rule::requiredIf(
                    fn() => $args['solution']['type'] === SolutionTypeEnum::INDOOR
                ),
            ],
            'solution.line_sets.*.line_set_id' => [
                'required',
                'int',
                Rule::exists(Solution::class, 'id')
                    ->where('type', SolutionTypeEnum::LINE_SET)
            ],
            'solution.indoors' => [
                'nullable',
                'array',
                Rule::requiredIf(
                    fn() => $args['solution']['type'] === SolutionTypeEnum::OUTDOOR
                ),
            ],
            'solution.indoors.*' => [
                'required',
                'int',
                Rule::exists(Solution::class, 'id')
                    ->where('type', SolutionTypeEnum::INDOOR)
            ],
            'solution.default_schemas' => [
                'nullable',
                'array',
                Rule::requiredIf(
                    fn(
                    ) => $args['solution']['type'] === SolutionTypeEnum::OUTDOOR && $args['solution']['zone'] === SolutionZoneEnum::MULTI
                ),
                new DefaultSchemasRule($args['solution'])
            ],
        ];
    }

    /**
     * @param $root
     * @param array $args
     * @param $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Product
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Product
    {
        return makeTransaction(
            fn() => $this->service->createUpdate(
                SolutionDto::byArgs($args['solution'])
            )
        );
    }
}

