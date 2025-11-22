<?php


namespace App\GraphQL\Queries\BackOffice\Users;


use App\GraphQL\Types\Users\UserType;
use App\Models\Users\User;
use App\Permissions\Users\UserShowPermission;
use App\Services\Users\UserService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class UsersQuery extends BaseQuery
{
    public const NAME = 'users';
    public const PERMISSION = UserShowPermission::KEY;

    public function __construct(private UserService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        $args = $this->buildArgs(
            User::ALLOWED_SORTING_FIELDS,
            [
                'first_name',
                'last_name',
                'second_name',
                'full_name',
                'email',
                'phone',
                'branch.name'
            ]
        );

        $args['sort']['defaultValue'] = [
            'full_name-asc',
            'branch_name-asc',
        ];

        return $args;
    }

    public function type(): Type
    {
        return UserType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->service->show($args, $fields->getRelations());
    }
}
