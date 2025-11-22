<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Solutions\Series;

use App\Dto\Catalog\Solutions\Series\SolutionSeriesDto;
use App\GraphQL\InputTypes\Catalog\Solutions\SolutionSeries\SolutionSeriesInputType;
use App\GraphQL\Types\Catalog\Solutions\SolutionSeriesType;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Permissions\Catalog\Solutions\SolutionCreateUpdatePermission;
use App\Services\Catalog\Solutions\SolutionSeriesService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SolutionSeriesCreateMutation extends BaseMutation
{
    public const NAME = 'solutionSeriesCreate';
    public const PERMISSION = SolutionCreateUpdatePermission::KEY;

    public function __construct(protected SolutionSeriesService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'input' => [
                'type' => SolutionSeriesInputType::nonNullType()
            ],
        ];
    }

    public function type(): Type
    {
        return SolutionSeriesType::nonNullType();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return SolutionSeries
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): SolutionSeries {
        return makeTransaction(
            fn() => $this->service->create(
                SolutionSeriesDto::byArgs($args['input'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.slug' => ['required', 'string', Rule::unique(SolutionSeries::class, 'slug')]
        ];
    }
}
