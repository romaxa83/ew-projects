<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Solutions;

use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Solutions\Solution;
use App\Permissions\Catalog\Solutions\SolutionDeletePermission;
use App\Services\Catalog\Solutions\SolutionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SolutionDeleteMutation extends BaseMutation
{
    public const NAME = 'solutionDelete';
    public const PERMISSION = SolutionDeletePermission::KEY;

    public function __construct(protected SolutionService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'product_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Solution::class, 'product_id')
                ]
            ]
        ];
    }

    /**
     * @param $root
     * @param array $args
     * @param $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool|null
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): ?bool
    {
        return makeTransaction(
            fn() => $this->service->delete(
                Solution::whereProductId($args['product_id'])
                    ->first()
            )
        );
    }
}

