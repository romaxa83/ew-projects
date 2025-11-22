<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Solutions\Series;

use App\Dto\Catalog\Solutions\Series\SolutionSeriesDto;
use App\GraphQL\InputTypes\Catalog\Solutions\SolutionSeries\SolutionSeriesInputType;
use App\GraphQL\Types\Catalog\Solutions\SolutionSeriesType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Permissions\Catalog\Solutions\SolutionCreateUpdatePermission;
use App\Services\Catalog\Solutions\SolutionSeriesService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SolutionSeriesUpdateMutation extends BaseMutation
{
    public const NAME = 'solutionSeriesUpdate';
    public const PERMISSION = SolutionCreateUpdatePermission::KEY;

    public function __construct(protected SolutionSeriesService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(SolutionSeries::class, 'id')],
            ],
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
            fn() => $this->service->update(
                SolutionSeries::find($args['id']),
                SolutionSeriesDto::byArgs($args['input'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.slug' => [
                'required',
                'string',
                Rule::unique(SolutionSeries::class, 'slug')->ignore($args['id'])
            ]
        ];
    }
}