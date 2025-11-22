<?php


namespace App\GraphQL\Mutations\BackOffice\Branches;


use App\Dto\Branches\BranchDto;
use App\GraphQL\InputTypes\Branches\BranchInputType;
use App\GraphQL\Types\Branches\BranchType;
use App\Models\Branches\Branch;
use App\Permissions\Branches\BranchCreatePermission;
use App\Services\Branches\BranchService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class BranchCreateMutation extends BaseMutation
{
    public const NAME = 'branchCreate';
    public const PERMISSION = BranchCreatePermission::KEY;

    public function __construct(private BranchService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
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
            fn() => $this->service->create(
                BranchDto::byArgs($args['branch'])
            )
        );
    }
}
