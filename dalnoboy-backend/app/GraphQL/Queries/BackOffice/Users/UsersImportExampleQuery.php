<?php


namespace App\GraphQL\Queries\BackOffice\Users;


use App\GraphQL\Types\DownloadType;
use App\Permissions\Users\UserCreatePermission;
use App\Services\Users\UserService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class UsersImportExampleQuery extends BaseQuery
{
    public const NAME = 'usersImportExample';
    public const PERMISSION = UserCreatePermission::KEY;

    public function __construct(private UserService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return DownloadType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): array {
        return $this->service->importExample();
    }
}
