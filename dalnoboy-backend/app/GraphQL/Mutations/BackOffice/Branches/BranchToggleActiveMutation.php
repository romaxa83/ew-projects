<?php


namespace App\GraphQL\Mutations\BackOffice\Branches;


use App\GraphQL\Types\Branches\BranchType;
use App\GraphQL\Types\NonNullType;
use App\Models\Branches\Branch;
use App\Permissions\Branches\BranchUpdatePermission;
use App\Services\Branches\BranchService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class BranchToggleActiveMutation extends BaseMutation
{
    public const NAME = 'branchToggleActive';
    public const PERMISSION = BranchUpdatePermission::KEY;

    public function __construct(private BranchService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Branch::class, 'id'),
                ]
            ],
        ];
    }

    public function type(): Type
    {
        return BranchType::nonNullType();
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Branch
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Branch
    {
        return makeTransaction(
            fn() => $this->service->toggleActive(
                Branch::find($args['id'])
            )
        );
    }
}
