<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Solutions\Series;

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

class SolutionSeriesDeleteMutation extends BaseMutation
{
    public const NAME = 'solutionSeriesDelete';
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
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
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
    ): bool {
        return makeTransaction(
            fn() => $this->service->delete(
                SolutionSeries::find($args['id'])
            )
        );
    }
}