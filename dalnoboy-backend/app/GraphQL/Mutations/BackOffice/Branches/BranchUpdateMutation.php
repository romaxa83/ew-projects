<?php


namespace App\GraphQL\Mutations\BackOffice\Branches;


use App\Dto\Branches\BranchDto;
use App\GraphQL\InputTypes\Branches\BranchInputType;
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

class BranchUpdateMutation extends BaseMutation
{
    public const NAME = 'branchUpdate';
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
            'branch' => [
                'type' => BranchInputType::nonNullType()
            ]
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
            fn() => $this->service->update(
                BranchDto::byArgs($args['branch']),
                Branch::find($args['id'])
            )
        );
    }
}
